<?php

declare(strict_types=1);

namespace App\Support\Finance;

use Academe\PhpFinance\General\Utils;
use Academe\PhpFinance\Returns\TSeries;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;

final class MarketData
{
    private const INDEX_MAP = [
        'IBOV'     => '^BVSP',
        'IBOVESPA' => '^BVSP',
        'IBSP39'   => '^IBSP',
    ];

    public static function yahooSymbol(string $b3): string
    {
        $b3 = strtoupper(trim($b3));
        $name = str_replace([' ', '.'], ['', '.'], $b3);
        if (isset(self::INDEX_MAP[$b3])) {
            return self::INDEX_MAP[$b3];
        }
        if (str_starts_with($name, '^')) {
            return $name;
        }
        return $name . '.SA';
    }

    public static function closes(string $b3, int $days = 365): array
    {
        if (!Online::is()) {
            return [];
        }
        $symbol  = self::yahooSymbol($b3);
        $start   = date('Y-m-d H:i:s', strtotime('-' . max(30, $days) . ' days'));
        $end     = date('Y-m-d H:i:s');
        $caches  = [];
        try {
            $caches = FetcherFactory::v8Chart($symbol, strtotime($start));
        } catch (\Throwable) {
            $caches = [];
        }
        $result = [];
        foreach ($caches as $r) {
            if (!isset($r['Date'], $r['Close'])) {
                continue;
            }
            if ($r['Close'] === null || !is_numeric((string) $r['Close']) || (float) $r['Close'] <= 0) {
                continue;
            }
            $result[(string) $r['Date']] = (float) $r['Close'];
        }
        ksort($result);
        return $result;
    }


    public static function spot(string $b3, int $windows = 10): ?float
    {
        $closes = self::closes($b3, max(30, $windows + 5));
        if ($closes === []) {
            return null;
        }
        return end($closes);
    }

    public static function returns(array $closes): array
    {
        $dates  = array_keys($closes);
        $result = [];
        for ($i = 1, $n = count($closes); $i < $n; $i++) {
            $result[$dates[$i]] = $closes[$dates[$i]] / $closes[$dates[$i - 1]] - 1.0;
        }
        return $result;
    }

    public static function annualizedVolatility(array $closes): ?float
    {
        if (count($closes) < 20) {
            return null;
        }
        $rets = array_values(self::returns($closes));
        if ($rets === []) {
            return null;
        }
        return Utils::annualizedVolatility($rets, 252);
    }

    public static function stats(string $b3, int $days = 365): ?array
    {
        $closes = self::closes($b3, $days);
        if (count($closes) < 30) {
            return null;
        }
        $rets       = self::returns($closes);
        $series     = new TSeries(array_values($rets), array_keys($rets), 'D');
        $rfr        = ReferenceRates::riskFreeRate()[0];

        return [
            'spot'                 => end($closes),
            'last_close'           => end(array_keys($closes)),
            'observations'         => count($rets),
            'cumulative_return'    => $series->cumRet(),
            'annualized_return'    => $series->anlzdRet() * 100,
            'volatility'           => $series->std() * sqrt(252),
            'sharpe'               => $series->sharpeRatio($rfr),
            'sortino'              => $series->sortino(),
            'calmar'               => Utils::calmarRatio(array_values($rets), 252),
            'max_drawdown'         => $series->maxDrawdown(),
            'var95'                => Utils::valueAtRisk(array_values($rets), 0.95),
            'cvar95'               => Utils::conditionalValueAtRisk(array_values($rets), 0.95),
        ];
    }
}
