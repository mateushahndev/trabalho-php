<?php

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\ProfileController;
use App\Controllers\TransactionController;
use App\Controllers\PositionController;
use App\Controllers\ContractController;

// --- Autenticação ---
$router->get('/', [AuthController::class, 'showLogin']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);

$router->get('/register', [AuthController::class, 'showRegister']);
$router->get('/registro', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->post('/registro', [AuthController::class, 'register']);

$router->get('/logout', [AuthController::class, 'logout']);

// --- Dashboard ---
$router->get('/dashboard', [DashboardController::class, 'index']);

// --- Perfil ---
$router->get('/profile', [ProfileController::class, 'edit']);
$router->get('/perfil', [ProfileController::class, 'edit']);
$router->post('/profile', [ProfileController::class, 'update']);
$router->post('/perfil', [ProfileController::class, 'update']);

// --- Transações ---
$router->get('/transactions', [TransactionController::class, 'index']);
$router->get('/transacoes', [TransactionController::class, 'index']);
$router->get('/transactions/create', [TransactionController::class, 'create']);
$router->get('/transacoes/create', [TransactionController::class, 'create']);
$router->post('/transactions', [TransactionController::class, 'store']);

// --- Contratos ---
$router->get('/contracts', [ContractController::class, 'index']);
$router->get('/contratos', [ContractController::class, 'index']);

// Abertura do formulário de criação (suporta inglês e português)
$router->get('/contracts/create', [ContractController::class, 'create']);
$router->get('/contratos/create', [ContractController::class, 'create']);
$router->get('/contratos/novo', [ContractController::class, 'create']);

// Processamento da criação (POST)
$router->post('/contracts', [ContractController::class, 'store']);
$router->post('/contratos', [ContractController::class, 'store']);

// Edição de contrato
$router->get('/contracts/{id}/edit', [ContractController::class, 'edit']);
$router->get('/contratos/{id}/editar', [ContractController::class, 'edit']);
$router->post('/contracts/{id}', [ContractController::class, 'update']);
$router->post('/contratos/{id}', [ContractController::class, 'update']);