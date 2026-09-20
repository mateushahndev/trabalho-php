<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\User;

final class ProfileController extends Controller
{
    public function edit(): void
    {
        $this->requireAuth();
        $user = $this->currentUser();
        $arr  = $user->toArray();

        if ($arr === null) {
            Session::remove('user_id');
            Session::flash('error', 'Sessão expirada. Faça login novamente.');
            $this->redirect('/login');
            return;
        }

        $old = [
            'nome'              => (string) $arr['nome'],
            'email'             => (string) $arr['email'],
            'senha'             => '',
            'senha_confirmacao' => '',
        ];

        $this->view('profile/form', [
            'pageTitle' => 'Perfil - Opções B3',
            'activeNav' => 'perfil',
            'user'      => $arr,
            'errors'    => [],
            'old'       => $old,
        ]);
    }

    public function update(): void
    {
        $this->requireAuth();
        $user = $this->currentUser();
        $arr  = $user->toArray();

        if ($arr === null) {
            Session::remove('user_id');
            Session::flash('error', 'Sessão expirada. Faça login novamente.');
            $this->redirect('/login');
            return;
        }

        $uid = (int) $arr['id'];

        $data = [
            'nome'              => $this->input('nome') ?? '',
            'email'             => strtolower($this->input('email') ?? ''),
            'senha'             => $this->input('senha') ?? '',
            'senha_confirmacao' => $this->input('senha_confirmacao') ?? '',
        ];

        // Confirmação só faz sentido se senha não for vazia 
        if ($data['senha'] === '' && $data['senha_confirmacao'] !== '') {
            $data['senha_confirmacao'] = '';
        }

        $hasPassword = $data['senha'] !== '';
        $errors = Validator::make($data, [
            'nome'              => ['required', 'min:3', 'max:120'],
            'email'             => ['required', 'email', 'max:160'],
            'senha'             => $hasPassword ? ['required', 'min:6', 'max:255'] : [],
            'senha_confirmacao' => $hasPassword ? ['required', 'min:6'] : [],
        ])->errors();

        if ($hasPassword && !isset($errors['senha']) && $data['senha'] !== $data['senha_confirmacao']) {
            $errors['senha_confirmacao'] = 'A confirmação de senha não coincide.';
        }

        // Email deve ser único
        if ($errors === [] && strtolower((string) $arr['email']) !== strtolower((string) $data['email'])) {
            if (User::findByEmail((string) $data['email']) !== null) {
                $errors['email'] = 'Este e-mail já está em uso por outra conta.';
            }
        }

        if ($errors !== []) {
            $this->storeOldInput($data);
            $this->view('profile/form', [
                'pageTitle' => 'Perfil - Opções B3',
                'activeNav' => 'perfil',
                'user'      => $arr,
                'errors'    => $errors,
                'old'       => $data,
            ]);
            return;
        }

        $user->updateProfile((string) $data['nome'], (string) $data['email']);
        if ($hasPassword) {
            $user->updatePassword(password_hash((string) $data['senha'], PASSWORD_DEFAULT));
        }

        // Atualiza nome da sessão caso tenha mudado 
        Session::set('user_nome', (string) $data['nome']);
        Session::flash('success', 'Perfil atualizado com sucesso!');
        $this->redirect('/perfil');
    }
}
