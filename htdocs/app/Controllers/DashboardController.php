<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Position;
use App\Models\Transaction;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $user   = $this->currentUser();
        $userId = (int) $user->toArray()['id'];

        // Fetch open positions with market data for P&L
        $positions = Position::forUser($userId, 'ABERTA');

        // Recent transactions (default 5, capped at 20)
        $limitIn = $this->input('limit');
        $transactionsLimit = ($limitIn !== null && ctype_digit($limitIn)) ? max(1, min(20, (int) $limitIn)) : 5;
        $recent = Transaction::recent($userId, $transactionsLimit);

        // Compute dashboard stats
        $invested    = 0.0;
        $unrealizedPnl  = 0.0;
        foreach ($positions as $p) {
            $qtd       = (int) $p['quantidade_aberta'];
            $invested += (float) $p['preco_medio'] * $qtd;
            $unrealizedPnl += ((float) $p['preco_mercado'] - (float) $p['preco_medio']) * $qtd;
        }

        $this->view('dashboard/index', [
            'pageTitle'     => 'Dashboard - Opções B3',
            'activeNav'     => 'dashboard',
            'stats'         => [
                'positions' => count($positions),
                'invested'  => $invested,
                'pnl'       => $unrealizedPnl,
                'trades'    => (int)$this->transactionCountFor($userId),
            ],
            'positions'     => $positions,
            'transactions'  => $recent,
	    'layout'        => 'layouts/main',
        ]);
    }

    private function transactionCountFor(int $userId): int
    {
        return Transaction::countForUser($userId);
    }
}

