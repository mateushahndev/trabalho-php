<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Contract;
use App\Support\Finance\MarketData;
use App\Support\Finance\Options;
use App\Support\Finance\Online;
use App\Support\Finance\ReferenceRates;

final class MarketController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $online   = Online::is();
        $rfrData  = ReferenceRates::riskFreeRate();
        $rfr      = $rfrData[0];
        $rfrLabel = $rfrData[1];

        $perPage = max(1, (int) config('pagination.markets_per_page', 6));
        $total   = Contract::countDistinctAtivos();
        $pages   = max(1, intdiv($total + $perPage - 1, $perPage));

        $page   = $this->page($pages);

        $pageAtivos = Contract::distinctAtivos($perPage, ($page - 1) * $perPage);

        $underlyings = [];
        foreach ($pageAtivos as $a) {
            if ($online) {
                $closes = MarketData::closes($a, (int) config('finance.history_days', 365));
                $stats  = count($closes) >= 30 ? MarketData::stats($a, (int) config('finance.history_days', 365)) : null;
                $symbol = MarketData::yahooSymbol($a);
            } else {
                $closes = [];
                $stats  = null;
                $symbol = '?';
            }
            $chartCloses = $online ? MarketData::closes($a, (int) config('finance.chart_days', 365)) : [];

            $underlyings[] = [
                'ativo'    => $a,
                'symbol'   => $symbol,
                'spot'     => $online && $chartCloses !== [] ? end($chartCloses) : null,
                'stats'    => $stats,
                'closes'   => array_slice(array_values($chartCloses), -(int) config('finance.chart_days', 365)),
            ];
        }

        $this->view('markets/index', [
            'pageTitle'     => 'Mercados & Preços - Opções B3',
            'activeNav'     => 'mercados',
            'online'        => $online,
            'rfr'           => $rfr,
            'rfr_label'     => $rfrLabel,
            'underlyings'   => $underlyings,
            'pagination'    => [
                'page'     => $page,
                'pages'    => $pages,
                'per_page' => $perPage,
                'total'    => $total,
            ],
            'layout'        => 'layouts/main',
        ]);
    }
}
