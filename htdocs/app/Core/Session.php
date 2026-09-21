<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        if (session_status() === PHP_SESSION_NONE) {
            session_save_path(sys_get_temp_dir());
        }
        session_name((string) config('session.name', 'opcoes_b3_session'));
        session_set_cookie_params([
            'httponly' => true,
            'samesite' => 'Strict', // quebra navegação a partir de links externos
            'secure'   => false, // mudar para true em produção com HTTPS
        ]);
        session_start();
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly'],
            );
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public static function getOldInput(): array
    {
        $data = $_SESSION['_old'] ?? [];
        unset($_SESSION['_old']);
        return $data;
    }

    public static function setOldInput(array $data): void
    {
        $_SESSION['_old'] = $data;
    }
}
