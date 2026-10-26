<?php

declare(strict_types=1);

namespace App\Support\Finance;

final class ReferenceRates
{
    private static ?array $cached = null;

    public static function riskFreeRate(): array
    {
        if (self::$cached !== null) {
            return self::$cached;
        }

        if (!Online::is()) {
            return self::$cached = [(float) config('finance.risk_free_fallback', 0.14), 'sem conexão (padrão)'];
        }

        $default = (float) config('finance.risk_free_fallback', 0.14);

        $apiKey = config('finance.fred_api_key');
        if (!$apiKey) {
            $apiKey = getenv('FRED_API_KEY') ?: null;
        }
        if (is_string($apiKey) && $apiKey !== '') {
            try {
                $obs = FetcherFactory::fetcher()->getFRED('DGS10', date('Y-m-d', strtotime('-3 years')), null, $apiKey);
                if ($obs !== []) {
                    $last = end($obs);
                    if (isset($last['value']) && is_numeric((string) $last['value'])) {
                        return self::$cached = [(float) $last['value'] / 100.0, 'FRED DGS10'];
                    }
                    prev($obs);
                    if (isset($obs[0]['value']) && is_numeric((string) $obs[0]['value'])) {
                        return self::$cached = [(float) $obs[0]['value'] / 100.0, 'FRED DGS10'];
                    }
                }
            } catch (\Throwable) {
            }
        }
        return self::$cached = [$default, '(config padrão)'];
    }
}
