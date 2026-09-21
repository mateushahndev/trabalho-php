<?php

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\ProfileController;
use App\Controllers\TransactionController;
use App\Controllers\PositionController;
use App\Controllers\ContractController;

// Raiz
$router->get('/', [DashboardController::class, 'index']);

// Login
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);

// Registro
$router->get('/registro', [AuthController::class, 'showRegister']);
$router->post('/registro', [AuthController::class, 'register']);

// Logout
$router->post('/logout', [AuthController::class, 'logout']);

// Perfil
$router->get('/perfil', [ProfileController::class, 'edit']);
$router->post('/perfil', [ProfileController::class, 'update']);

// Transações
$router->get('/transacoes', [TransactionController::class, 'index']);
$router->get('/transacoes/novo', [TransactionController::class, 'create']);
$router->post('/transacoes', [TransactionController::class, 'store']);

// Contratos
$router->get('/contratos', [ContractController::class, 'index']);
$router->get('/contratos/novo', [ContractController::class, 'create']);
$router->post('/contratos', [ContractController::class, 'store']);
$router->get('/contratos/{id}/editar', [ContractController::class, 'edit']);
$router->post('/contratos/{id}', [ContractController::class, 'update']);

// Posições
$router->get('/posicoes',           [PositionController::class, 'index']);
$router->get('/posicoes/novo',      [PositionController::class, 'create']);
$router->post('/posicoes',          [PositionController::class, 'store']);
$router->get('/posicoes/{id}/fechar',[PositionController::class, 'closeForm']);
$router->post('/posicoes/{id}/fechar', [PositionController::class, 'close']);
