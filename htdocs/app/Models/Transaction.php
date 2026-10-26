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

    public static function forUser(int $userId, ?string $operacao = null, ?int $limit = null, int $offset = 0): array
    {
        [$where, $params] = self::userFilter($userId, $operacao);
        $sql = 'SELECT t.*, c.ativo, c.tipo_opcao, c.preco_exercicio, c.data_vencto'
            . ' FROM transacoes t'
            . ' JOIN contratos c ON c.id = t.contrato_id'
            . ' WHERE ' . $where
            . ' ORDER BY t.data_transacao DESC, t.id DESC';
        if ($limit !== null) {
            $sql .= sprintf(' LIMIT %d OFFSET %d', max(0, $limit), max(0, $offset));
        }

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function countForUser(int $userId, ?string $operacao = null): int
    {
        [$where, $params] = self::userFilter($userId, $operacao);
        $stmt = self::db()->prepare('SELECT COUNT(*) FROM transacoes t WHERE ' . $where);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    private static function userFilter(int $userId, ?string $operacao): array
    {
        $where  = 't.usuario_id = :uid';
        $params = ['uid' => $userId];
        if ($operacao !== null && in_array($operacao, ['COMPRA', 'VENDA'], true)) {
            $where .= ' AND t.operacao = :op';
            $params['op'] = $operacao;
        }
        return [$where, $params];
    }

    public static function recent(int $userId, int $limit = 5): array
    {
        if ($limit <= 0) {
            return [];
        }

        $stmt = self::db()->prepare(
            'SELECT t.*, c.ativo, c.tipo_opcao, c.preco_exercicio'
            . ' FROM transacoes t JOIN contratos c ON c.id = t.contrato_id'
            . ' WHERE t.usuario_id = :uid'
            . ' ORDER BY t.data_transacao DESC, t.id DESC'
            . sprintf(' LIMIT %d', max(1, $limit))
        );
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }
}
