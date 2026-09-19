<?php
declare(strict_types=1);

namespace App\Core;

use \RuntimeException;

final class View
{
    public static function render(string $template, array $data = []): string
    {
        $viewFile  = self::path($template);
        $layoutKey = 'layout';

        // Extract variables into the view scope
        extract($data, EXTR_SKIP);

        ob_start();
        include $viewFile;
        $content = (string) ob_get_clean();

        $layoutFile = self::path($data[$layoutKey] ?? 'layouts/main');
        ob_start();
        include $layoutFile;
        return (string) ob_get_clean();
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
