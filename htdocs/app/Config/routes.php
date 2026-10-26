<?php
declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\ContractController;
use App\Controllers\PositionController;
use App\Controllers\TransactionController;
use App\Controllers\ProfileController;
use App\Controllers\AnalysisController;
use App\Controllers\MarketController;

$router->get('/', [DashboardController::class, 'index']);

$router->get('/login',    [AuthController::class, 'showLogin']);
$router->post('/login',   [AuthController::class, 'login']);
$router->get('/registro', [AuthController::class, 'showRegister']);
$router->post('/registro',[AuthController::class, 'register']);
$router->post('/logout',  [AuthController::class, 'logout']);

$router->get('/contratos',       [ContractController::class, 'index']);
$router->get('/contratos/novo',  [ContractController::class, 'create']);
$router->post('/contratos',      [ContractController::class, 'store']);
$router->get('/contratos/{id}/editar', [ContractController::class, 'edit']);
$router->post('/contratos/{id}',     [ContractController::class, 'update']);

$router->get('/posicoes',           [PositionController::class, 'index']);
$router->get('/posicoes/novo',      [PositionController::class, 'create']);
$router->post('/posicoes',          [PositionController::class, 'store']);
$router->get('/posicoes/{id}/fechar',[PositionController::class, 'closeForm']);
$router->post('/posicoes/{id}/fechar', [PositionController::class, 'close']);

$router->get('/transacoes',         [TransactionController::class, 'index']);
$router->get('/transacoes/novo',    [TransactionController::class, 'create']);
$router->post('/transacoes',        [TransactionController::class, 'store']);

$router->get('/perfil',   [ProfileController::class, 'edit']);
$router->post('/perfil',  [ProfileController::class, 'update']);

$router->get('/analise', [AnalysisController::class, 'index']);
$router->get('/mercados', [MarketController::class, 'index']);
$router->get('/preview-bs', [ContractController::class, 'preview']);
