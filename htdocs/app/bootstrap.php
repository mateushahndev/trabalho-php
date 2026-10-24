<?php
declare(strict_types=1);

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Carrega o Autoload do Composer (vendor/autoload.php)
if (is_file(BASE_PATH . '/vendor/autoload.php')) {
    require BASE_PATH . '/vendor/autoload.php';
} else {
    // Fallback: autoloader manual (caso o professor rode sem composer install)
    spl_autoload_register(static function (string $class): void {
        $prefix = 'App\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }
        $relativeClass = substr($class, strlen($prefix));
        $path = BASE_PATH . '/app/' . str_replace('\\', '/', $relativeClass) . '.php';
        if (is_file($path)) {
            require $path;
        }
    });
}

function config(string $key, mixed $default = null): mixed
{
    static $config = null;
    if ($config === null) {
        $config = require BASE_PATH . '/app/Config/config.php';
    }
    $value = $config;
    foreach (explode('.', $key) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }
    return $value;
}

function e(mixed $value): string
{
    if ($value === null) {
        return '';
    }
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function money(float|int|string $value, int $decimals = 2): string
{
    return number_format((float) $value, $decimals, ',', '.');
}

function br_date(mixed $value): string
{
    if ($value === null || $value === '') {
        return date('Y-m-d\TH:i:s');
    }
    // Usa o pacote Carbon se estiver disponível (Composer)
    if (class_exists(\Carbon\Carbon::class)) {
        try {
            return \Carbon\Carbon::parse($value)->format('d/m/Y H:i');
        } catch (\Throwable) {
            // fallback abaixo
        }
    }
    // Fallback manual
    $value = (string) $value;
    foreach (['Y-m-d H:i:s', 'Y-m-d\TH:i:s', 'Y-m-d\TH:i', 'Y-m-d'] as $format) {
        $date = \DateTime::createFromFormat($format, $value);
        if ($date !== false) {
            return str_contains((string) $format, 'H')
                ? $date->format('d/m/Y H:i')
                : $date->format('d/m/Y');
        }
    }
    return $value;
}
