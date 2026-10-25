<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Position;
use App\Support\Finance\MarketData;
use App\Support\Finance\Options;
use App\Support\Finance\Online;
use App\Support\Finance\ReferenceRates;
use App\Support\Finance\Analysis as FinanceAnalysis;

final class AnalysisController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $userId = (int) $this->currentUser()->toArray()['id'];

        $online   = Online::is();
        $rfrData  = ReferenceRates::riskFreeRate();
        $rfr      = $rfrData[0];
        $rfrLabel = $rfrData[1];

        $openPositions = Position::forUser($userId, 'ABERTA');
        $greeks        = null;
        if ($openPositions && $online) {
            $ativoList   = array_unique(array_column($openPositions, 'ativo'));
            $marketCache = [];
            foreach ($ativoList as $a) {
                $closes      = MarketData::closes($a, 365);
                $marketCache[$a] = $closes !== []
                    ? ['spot' => end($closes), 'vol' => MarketData::annualizedVolatility($closes) ?? config('finance.vol_fallback', 0.35)]
                    : null;
            }
            $provider = static fn ($a) => $marketCache[$a] !== null ? [...$marketCache[$a], 'rfr' => $rfr] : null;
            $greeks   = Options::portfolioGreeks($openPositions, $provider);
        }

        $tradeMetrics = $online ? FinanceAnalysis::tradeMetrics($userId) : null;

        $benchmark = $online ? FinanceAnalysis::benchmark($userId) : null;

        $rollingBeta = $online ? FinanceAnalysis::rollingBeta($userId) : null;

        $factor5 = $online
            ? FinanceAnalysis::factorModel5F($userId)
            : ['zip_unavailable' => true, 'zip_note' => 'Sem conexão — dados diários indisponíveis.'];

        $this->view('analysis/index', [
            'pageTitle'     => 'Análise & Risco · Opções B3',
            'activeNav'     => 'analise',
            'online'        => $online,
            'rfr'           => $rfr,
            'rfr_label'     => $rfrLabel,
            'greeks'        => $greeks,
            'tradeMetrics'  => $tradeMetrics,
            'benchmark'     => $benchmark,
            'rollingBeta'   => $rollingBeta,
            'factor5'       => $factor5,
            'layout'        => 'layouts/main',
       ]);
    }
}
