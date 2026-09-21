<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\User;

final class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (Session::has('user_id')) {
            $this->redirect('/');
        }
        $this->view('auth/login', [
            'pageTitle' => 'Entrar - Opções B3',
            'layout'    => 'layouts/auth',
            'errors'    => [],
            'old'       => [],
        ]);
    }

    public function login(): void
    {
        $email = strtolower($this->input('email') ?? '');
        $senha = $this->input('senha') ?? '';

        $errors = Validator::make([
            'email' => $email,
            'senha' => $senha,
        ], [
            'email' => ['required', 'email'],
            'senha' => ['required'],
        ])->errors();

        if ($errors !== []) {
            $this->viewAndFlash('auth/login', $errors, compact('email'), 'login');
            return;
        }

        $user = User::authenticate($email, $senha);
        if ($user === null) {
            $errors['senha'] = 'E-mail ou senha inválidos.';
            $this->viewAndFlash('auth/login', $errors, ['email' => $email], 'login');
            return;
        }

        // Regenera sessão para evitar ataques de fixação
        Session::remove('user_id');
        session_regenerate_id(true);
        Session::set('user_id', (int) $user['id']);
        Session::flash('success', 'Bem-vindo de volta, ' . e($user['nome']) . '!');
        $this->redirect('/');
    }

    public function showRegister(): void
    {
        if (Session::has('user_id')) {
            $this->redirect('/');
        }
        $this->viewAndFlashEmpty('auth/register', 'register', [
            'pageTitle' => 'Criar conta - Opções B3',
            'layout'    => 'layouts/auth',
        ]);
    }

    public function register(): void
    {
        $data = [
            'nome'              => $this->input('nome') ?? '',
            'email'             => strtolower($this->input('email') ?? ''),
            'senha'             => $this->input('senha') ?? '',
            'senha_confirmacao' => $this->input('senha_confirmacao') ?? '',
        ];

        $errors = Validator::make($data, [
            'nome'              => ['required', 'min:3', 'max:120'],
            'email'             => ['required', 'email', 'max:160'],
            'senha'             => ['required', 'min:6', 'max:255'],
            'senha_confirmacao' => ['required', 'min:6'],
        ])->errors();

        if (!isset($errors['senha']) && $data['senha'] !== $data['senha_confirmacao']) {
            $errors['senha_confirmacao'] = 'A confirmação de senha não coincide.';
        }

        if ($errors === [] && User::findByEmail((string) $data['email']) !== null) {
            $errors['email'] = 'Este e-mail já está cadastrado.';
        }

        if ($errors !== []) {
            $this->viewAndFlash('auth/register', $errors, $data, 'register');
            return;
        }

        $id = User::create((string) $data['nome'], (string) $data['email'], password_hash((string) $data['senha'], PASSWORD_DEFAULT));
        session_regenerate_id(true);
        Session::set('user_id', $id);
        Session::flash('success', 'Conta criada com sucesso! Bem-vindo, ' . e((string) $data['nome']) . '!');
        $this->redirect('/');
    }

    public function logout(): void
    {
        Session::destroy();
        Session::start(); // reinicia de propósito para ver o flash
        Session::flash('success', 'Você saiu da sua conta.');
        $this->redirect('/login');
    }

    private function viewAndFlash(string $view, array $errors, array $data, string $redirect): void
    {
        $this->storeOldInput($data);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
            unset($errors['_csrf']); // don't show CSRF as field error
            if ($errors !== []) {
                $this->view($view, [
                    'pageTitle' => e(config('app.name')),
                    'layout'    => 'layouts/auth',
                    'errors'    => $errors,
                    'old'       => $data,
                ]);
                return;
            }
        }
        // Redireciona à origem com Flash
        if (isset($_SERVER['HTTP_REFERER'])) {
            Session::flash('error', 'Verifique os dados e tente novamente.');
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        $this->redirect('/login');
    }

    private function viewAndFlashEmpty(string $view, string $redirect, array $extra): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Session::flash('error', 'erro genérico (não esperado)');
            header('Location: /' . $redirect);
            exit;
        }
        $this->view($view, $extra + ['errors' => [], 'old' => []]);
    }
}
