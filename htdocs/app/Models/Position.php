<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use DomainException;

final class Position extends Model
{
    private const SELECT = 'SELECT p.*, c.ativo, c.tipo_opcao, c.preco_exercicio, c.data_vencto, c.preco_atual AS preco_mercado'
        . ' FROM posicoes p'
        . ' JOIN contratos c ON c.id = p.contrato_id';

    public static function forUser(int $userId, ?string $status = null): array
    {
        $sql = self::SELECT . ' WHERE p.usuario_id = :usuario_id';
        $params = ['usuario_id' => $userId];
        if ($status !== null && in_array($status, ['ABERTA', 'FECHADA'], true)) {
            $sql .= ' AND p.status = :status';
            $params['status'] = $status;
        }
        $sql .= ' ORDER BY p.data_abertura DESC, p.id DESC';

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $stmt = self::db()->prepare(self::SELECT . ' WHERE p.id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function findOpen(int $userId, int $contractId): ?array
    {
        $stmt = self::db()->prepare(
            self::SELECT . ' WHERE p.usuario_id = :usuario_id AND p.contrato_id = :contrato_id AND p.status = :status'
        );
        $stmt->execute([
            'usuario_id' => $userId,
            'contrato_id' => $contractId,
            'status'     => 'ABERTA',
        ]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function mergeOrInsert(int $userId, int $contractId, int $quantidade, float $precoMedio): void
    {
        $open = self::findOpen($userId, $contractId);
        if ($open !== null) {
            $totalAtual   = (int) $open['quantidade_total'];
            $abertaAtual  = (int) $open['quantidade_aberta'];
            $mediaAtual   = (float) $open['preco_medio'];
            $novaTotal    = $totalAtual + $quantidade;
            $novaAberta   = $abertaAtual + $quantidade;
            $novaMedia    = ($mediaAtual * $totalAtual + $precoMedio * $quantidade) / $novaTotal;

            self::db()->prepare(
                'UPDATE posicoes SET quantidade_total = :total, quantidade_aberta = :aberta, preco_medio = ROUND(preco_medio, 2) WHERE id = :id'
            )->execute(['total' => $novaTotal, 'aberta' => $novaAberta, 'media' => $novaMedia, 'id' => (int)$open['id']]);
        } else {
            self::db()->prepare(
                'INSERT INTO posicoes (usuario_id, contrato_id, quantidade_total, quantidade_aberta, preco_medio) VALUES (:uid, :cid, :qtd, :qtd, :pm)'
            )->execute([
                'uid' => $userId,
                'cid' => $contractId,
                'qtd' => $quantidade,
                'pm'  => round($precoMedio, 2),
            ]);
        }
    }

    public static function applyTrade(int $userId, int $contractId, string $operacao, int $quantidade, float $preco): void
    {
        if ($operacao === 'COMPRA') {
            self::mergeOrInsert($userId, $contractId, $quantidade, $preco);
            return;
        }

        $open = self::findOpen($userId, $contractId);
        if ($open === null) {
            throw new DomainException('Não há posição aberta para este contrato para vender.');
        }
        $abertaAtual  = (int) $open['quantidade_aberta'];
        if ($quantidade > $abertaAtual) {
            throw new DomainException(
                'A quantidade de venda (' . $quantidade . ') supera a posição aberta (' . $abertaAtual . ' contratos).'
            );
        }

        $novaAberta = $abertaAtual - $quantidade;
        if ($novaAberta === 0) {
            self::db()->prepare(
                "UPDATE posicoes SET quantidade_aberta = 0, status = 'FECHADA', data_fechamento = NOW() WHERE id = :id"
            )->execute(['id' => (int)$open['id']]);
        } else {
            self::db()->prepare(
                'UPDATE posicoes SET quantidade_aberta = :aberta WHERE id = :id'
            )->execute(['aberta' => $novaAberta, 'id' => (int)$open['id']]);
        }
    }

    public static function open(int $userId, int $contractId, int $quantidade, float $precoMedio): void
    {
        $pdo = self::db();
        $pdo->beginTransaction();
        try {
            self::mergeOrInsert($userId, $contractId, $quantidade, $precoMedio);
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
