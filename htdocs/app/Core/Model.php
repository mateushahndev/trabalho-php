<?php
declare(strict_types=1);

namespace App\Core;

use \PDO;
use \RuntimeException;

abstract class Model
{
    private static ?PDO $connection = null;

    protected static function db(): PDO
    {
        if (self::$connection === null) {
            $db  = config('database');
            $dsn = sprintf(
                'pgsql:host=%s;port=%d;dbname=%s',
                $db['host'],
                $db['port'],
                $db['dbname'],
            );

            self::$connection = new PDO($dsn, $db['user'], $db['password'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // padrão PHP 8+, lança exceções
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // arrays associativos com nome de coluna como chave
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$connection;
    }
}
