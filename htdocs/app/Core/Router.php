<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(string $pattern, array $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, array $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    private function add(string $method, string $pattern, array $handler): void
    {
        $this->routes[$method][] = ['pattern' => $pattern, 'handler' => $handler];
    }

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);

        $csrfValid = $method !== 'POST' || Csrf::valid($_POST['_csrf'] ?? null);

        $path = parse_url($uri, PHP_URL_PATH) ?? '/';
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        foreach ($this->routes[$method] ?? [] as $route) {
            if (preg_match(self::toRegex($route['pattern']), $path, $matches)) {
                $params = [];
                foreach ($matches as $k => $v) {
                    if (is_string($k)) $params[$k] = $v;
                }

                if (!$csrfValid) {
                    Session::flash('error', 'Token de segurança inválido. Tente novamente.');
                    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
                    exit;
                }

                $controllerClass = $route['handler'][0];
                $action          = $route['handler'][1];
                $controller      = new $controllerClass();
                $controller->setParams($params);
                $controller->{$action}();
                return;
            }
        }

        if ($method === 'POST') {
            Session::flash('error', $csrfValid ? 'Esta ação não está disponível.' : 'Token de segurança inválido. Tente novamente.');
            header('Location: /');
            exit;
        }

        View::errorPage(404, 'A página que você procura não existe ou foi removida.');
    }

    private static function toRegex(string $pattern): string
    {
        /* Substitui p.ex. {id} na Rota por um grupo de captura
	 * nomeado 'id' para ser usado com preg_match()
	 */
        $regex = preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $pattern);
        return '#^' . ($regex ?? '') . '$#u';
    }
}
