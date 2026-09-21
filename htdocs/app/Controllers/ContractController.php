<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;

final class ContractController extends Controller
{
    public function __construct()
    {
        if (!Session::has('user_id')) {
            $this->redirect('/login');
        }
    }

    public function index(): void
    {
        $this->view('contracts/index', [
            'pageTitle' => 'Contratos - Opções B3',
        ]);
    }

    public function create(): void
    {
        $this->view('contracts/create', [
            'pageTitle' => 'Novo Contrato - Opções B3',
        ]);
    }

    public function store(): void
    {
        $this->redirect('/contracts');
    }

    public function edit(string $id = ''): void
    {
        $this->view('contracts/edit', [
            'pageTitle' => 'Editar Contrato - Opções B3',
            'id' => $id,
        ]);
    }

    public function update(string $id = ''): void
    {
        $this->redirect('/contracts');
    }
}