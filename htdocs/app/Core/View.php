<?php
declare(strict_types=1);

namespace App\Core;

use \RuntimeException;

final class View
{
    public static function render(string $template, array $data = []): string
    {
        $viewFile  = self::path($template ?? '');
        $layoutKey = 'layout';

        $skipLayout = !array_key_exists($layoutKey, $data);

        extract($data, EXTR_SKIP);

        ob_start();
        include $viewFile;
        $content = (string) ob_get_clean();

        if ($skipLayout) {
            return $content;
        }

        $layoutFile = self::path($data[$layoutKey] ?? 'layouts/main');
        ob_start();
        include $layoutFile;
        return (string) ob_get_clean();
    }

    public static function renderFragment(string $template, array $data = []): string
    {
        $viewFile = self::path($template ?? '');
        extract($data, EXTR_SKIP);
        ob_start();
        include $viewFile;
        return (string) ob_get_clean();
    }

    public static function errorPage(int $code, string $message): never
    {
        http_response_code($code);
        echo self::render('errors/error', [
            'pageTitle' => $code . ' — Erro',
            'layout'    => 'layouts/auth',
            'code'      => $code,
            'message'   => $message,
        ]);
        exit;
    }

    private static function path(string $template): string
    {
        $viewPath = BASE_PATH . '/app/Views/' . str_replace('\\', '/', $template) . '.php';

        if (!is_file($viewPath)) {
            throw new RuntimeException("View not found: {$template} (looked for {$viewPath})");
        }
        return $viewPath;
    }
}
