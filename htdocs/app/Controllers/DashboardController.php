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

        $positions = Position::forUser($userId, 'ABERTA');

        $transactionsLimit = 5;
        $recent = Transaction::recent($userId, $transactionsLimit);

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
        ]);
    }

    private function transactionCountFor(int $userId): int
    {
        return count(Transaction::forUser($userId));
    }
}

