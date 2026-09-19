<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class User extends Model
{
    private ?array $data = null;

    public function __construct(private readonly ?int $id = null) // $id vira variável de classe
    {
        if ($id !== null) {
            $this->data = self::findById($id);
        }
    }

    public static function findByEmail(string $email): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM usuarios WHERE lower(email) = lower(:e) LIMIT 1'
        );
        $stmt->execute(['e' => $email]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function findById(int $id): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM usuarios WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function create(string $nome, string $email, string $senhaHash): int
    {
        $stmt = self::db()->prepare(
            'INSERT INTO usuarios (nome, email, senha_hash) VALUES (:nome, :email, :senha)'
        );
        $stmt->execute([
            'nome'  => trim($nome),
            'email' => strtolower(trim($email)),
            'senha' => $senhaHash,
        ]);
        return (int) self::db()->lastInsertId('usuarios_id_seq');
    }

    public function updateProfile(string $nome, string $email): void
    {
        if ($this->id === null) return;
        $stmt = self::db()->prepare(
            'UPDATE usuarios SET nome = :nome, email = lower(:email) WHERE id = :id'
        );
        $stmt->execute(['nome' => trim($nome), 'email' => strtolower(trim($email)), 'id' => $this->id]);
    }

    public function updatePassword(string $senhaHash): void
    {
        if ($this->id === null) return;
        $stmt = self::db()->prepare('UPDATE usuarios SET senha_hash = :senha WHERE id = :id');
        $stmt->execute(['senha' => $senhaHash, 'id' => $this->id]);
    }

    public static function authenticate(string $email, string $senha): ?array
    {
        $user = self::findByEmail($email);
        if ($user === null) return null;
        if (!password_verify($senha, $user['senha_hash'])) return null;
        return $user;
    }

    public function toArray(): ?array
    {
        return $this->data;
    }

    public function exists(): bool
    {
        return $this->data !== null;
    }
}

