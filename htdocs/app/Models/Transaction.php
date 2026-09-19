<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Transaction extends Model
{
    public static function record(int $userId, int $contractId, string $operacao, int $quantidade, float $preco, float $comissao, string $dataTransacao): int
    {
        $pdo      = self::db();
        $stmtIns  = $pdo->prepare(
            'INSERT INTO transacoes (usuario_id, contrato_id, operacao, quantidade, preco, comissao, data_transacao) VALUES (:uid, :cid, :op, :qtd, :preco, :com, :dt)'
        );

        $pdo->beginTransaction();
        try {
            Position::applyTrade($userId, $contractId, $operacao, $quantidade, $preco);

            $stmtIns->execute([
                'uid'  => $userId,
                'cid'  => $contractId,
                'op'   => strtoupper($operacao),
                'qtd'  => $quantidade,
                'preco' => round($preco, 2),
                'com'  => round($comissao, 2),
                'dt'   => $dataTransacao,
            ]);

            $id = (int) $pdo->lastInsertId('transacoes_id_seq');
            $pdo->commit();
            return $id;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function forUser(int $userId, ?string $operacao = null): array
    {
        $sql  = 'SELECT t.*, c.ativo, c.tipo_opcao, c.preco_exercicio, c.data_vencto'
            . ' FROM transacoes t'
            . ' JOIN contratos c ON c.id = t.contrato_id'
            . " WHERE t.usuario_id = :uid";
        $params = ['uid' => $userId];
        if ($operacao !== null && in_array($operacao, ['COMPRA', 'VENDA'], true)) {
            $sql .= " AND t.operacao = :op";
            $params['op'] = $operacao;
        }

        $sql   = $sql . ' ORDER BY t.data_transacao DESC, t.id DESC';
        $stmt  = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function recent(int $userId, int $limit = 5): array
    {
        $stmt = self::db()->prepare(
            'SELECT t.*, c.ativo, c.tipo_opcao, c.preco_exercicio'
            . ' FROM transacoes t JOIN contratos c ON c.id = t.contrato_id'
            . " WHERE t.usuario_id = :uid"
            . ' ORDER BY t.data_transacao DESC, t.id DESC'
        );
        $stmt->execute(['uid' => $userId]);
        if ($limit > 0) {
            $rows = $stmt->fetchAll();
            return array_slice($rows, 0, $limit);
        }
        return [];
    }
}
