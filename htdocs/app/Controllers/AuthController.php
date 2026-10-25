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
            $this->reRenderForm('auth/login', $errors, compact('email'), 'Entrar - Opções B3');
            return;
        }

        $user = User::authenticate($email, $senha);
        if ($user === null) {
            $errors['senha'] = 'E-mail ou senha inválidos.';
            $this->reRenderForm('auth/login', $errors, ['email' => $email], 'Entrar - Opções B3');
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
        $this->view('auth/register', 'register', [
            'pageTitle' => 'Criar conta - Opções B3',
            'layout'    => 'layouts/auth',
            'errors'    => [],
            'old'       => [],
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
            $this->reRenderForm('auth/register', $errors, $data, 'Criar conta - Opções B3');
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

    private function reRenderForm(string $view, array $errors, array $old, string $pageTitle): void
    {
        $this->storeOldInput($old);
        unset($errors['_csrf']); // never surface the CSRF field as a form error
        $this->view($view, [
            'pageTitle' => $pageTitle,
            'layout'    => 'layouts/auth',
            'errors'    => $errors,
            'old'       => $old,
        ]);

    }
