<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Session;
use App\Models\User;

abstract class Controller
{
    protected array $params = [];

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    protected function param(string $name, ?string $default = null): ?string
    {
        return $this->params[$name] ?? $default;
    }

    protected function intParam(string $name): ?int
    {
        $value = $this->param($name);
        return is_string($value) && ctype_digit($value) ? (int) $value : null;
    }

    protected function view(string $template, array $data = []): void
    {
        if (isset($data['status'])) {
            http_response_code((int) $data['status']);
        }
        $hasSessionUser = Session::has('user_id');
        $userData = null;
        if ($hasSessionUser) {
            $u = new User((int) Session::get('user_id'));
            if ($u->exists()) {
                $userData = $u->toArray();
            } else {
                Session::remove('user_id');
            }
        }
        $data['user'] ??= $userData;

        echo View::render($template, $data);
    }

    protected function redirect(string $path): never
    {
        header('Location: ' . $path);
        exit;
    }

    protected function requireAuth(): void
    {
        if (!Session::has('user_id')) {
            Session::flash('error', 'Voce precisa estar logado para acessar esta area.');
            $this->redirect('/login');
        }
    }

    protected function currentUser(): User
    {
        return new User((int) Session::get('user_id'));
    }

    protected function input(string $key, ?string $default = null): ?string
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;
        if (is_string($value)) {
            $value = trim($value);
            return $value === '' ? null : $value;
        }
        return $default;
    }

    protected function storeOldInput(array $data): void
    {
        Session::setOldInput($data);
    }

    protected function notFound(): never
    {
        http_response_code(404);
        echo View::render('errors/error', [
            'pageTitle' => '404 - Pagina nao encontrada',
            'layout'    => 'layouts/auth',
            'code'      => 404,
            'message'   => 'A página que você está procurando não existe ou foi removida.',
        ]);
	exit;
    }
}
