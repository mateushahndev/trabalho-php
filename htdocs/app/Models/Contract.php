<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Contract extends Model
{
    public static function all(?int $limit = null, int $offset = 0): array
    {
        $sql = 'SELECT * FROM contratos ORDER BY data_vencto ASC, ativo ASC, preco_exercicio ASC, id ASC';
        if ($limit !== null) {
            $sql .= sprintf(' LIMIT %d OFFSET %d', max(0, $limit), max(0, $offset));
        }

        return self::db()->query($sql)->fetchAll();
    }

    public static function countAll(): int
    {
        return (int) self::db()->query('SELECT COUNT(*) FROM contratos')->fetchColumn();
    }

    public static function distinctAtivos(?int $limit = null, int $offset = 0): array
    {
        $sql = 'SELECT DISTINCT ativo FROM contratos ORDER BY ativo ASC';
        if ($limit !== null) {
            $sql .= sprintf(' LIMIT %d OFFSET %d', max(0, $limit), max(0, $offset));
        }

        $stmt = self::db()->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    public static function countDistinctAtivos(): int
    {
        $stmt = self::db()->query('SELECT COUNT(DISTINCT ativo) FROM contratos');
        return (int) $stmt->fetchColumn();
    }

    public static function findById(int $id): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM contratos WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function create(string $ativo, string $tipoOpcao, float $precoExercicio, string $dataVencto, float $precoAtual): int
    {
        $stmt = self::db()->prepare(
            'INSERT INTO contratos (ativo, tipo_opcao, preco_exercicio, data_vencto, preco_atual)
             VALUES (:ativo, :tipo, :strike, :vencto, :preco)'
        );
        $stmt->execute([
            'ativo'  => strtoupper(trim($ativo)),
            'tipo'   => strtoupper(trim($tipoOpcao)),
            'strike' => round($precoExercicio, 2),
            'vencto' => $dataVencto,
            'preco'  => round($precoAtual, 2),
        ]);
        return (int) self::db()->lastInsertId('contratos_id_seq');
    }

    public static function update(int $id, string $ativo, string $tipoOpcao, float $precoExercicio, string $dataVencto, float $precoAtual): void
    {
        $stmt = self::db()->prepare(
            'UPDATE contratos SET ativo = :ativo, tipo_opcao = :tipo, preco_exercicio = :strike, data_vencto = :vencto, preco_atual = :preco WHERE id = :id'
        );
        $stmt->execute([
            'ativo'  => strtoupper(trim($ativo)),
            'tipo'   => strtoupper(trim($tipoOpcao)),
            'strike' => round($precoExercicio, 2),
            'vencto' => $dataVencto,
            'preco'  => round($precoAtual, 2),
            'id'     => $id,
        ]);
    }

    public static function label(array $contract): string
    {
        return sprintf('%s · %s · R$ %s', (string) $contract['ativo'], (string) $contract['tipo_opcao'], money((float) $contract['preco_exercicio'], 2));
    }
}
