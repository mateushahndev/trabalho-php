<?php
declare(strict_types=1);

// Carrega bootstrap (autoloader + helpers)
require __DIR__ . '/../app/bootstrap.php';

use App\Core\Router;
use App\Core\Session;

$router = new Router();

// Carrega as rotas definidas em app/Config/routes.php ($router visível no escopo).
require __DIR__ . '/../app/Config/routes.php';

try {
    // Servidor embutido: sirva arquivos estáticos diretamente se existirem
    if (PHP_SAPI === 'cli-server') {
        $file = $_SERVER['DOCUMENT_ROOT'] . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if (is_file($file)) {
            return false; // deixa o servidor servir como arquivo estático
        }
    }

    Session::start();

    $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

} catch (\Throwable $e) {
    // Em produção, remover o debug: usar error_log + página genérica.
    if (config('app.debug', false)) {
        http_response_code(500);
        echo '<h1>Erro interno do servidor</h1>';
        echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
        echo '<small>' . htmlspecialchars($e->getFile() . ':' . $e->getLine()) . '</small>';
    } else {
        http_response_code(500);
        echo '<h1>Erro interno do servidor</h1><p>Ocorreu um erro inesperado.</p>';
    }
    error_log(sprintf('[%s] %s in %s:%d', date('c'), $e->getMessage(), $e->getFile(), $e->getLine()));
}

