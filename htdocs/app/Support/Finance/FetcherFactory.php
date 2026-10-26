<?php

declare(strict_types=1);

namespace App\Support\Finance;

use Academe\PhpFinance\Datasets\DataFetcher;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\RequestOptions;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

final class FetcherFactory
{
    private static ?DataFetcher $fetcher = null;
    private static ?CacheItemPoolInterface $cache = null;

    public static function cache(): CacheItemPoolInterface
    {
        if (self::$cache === null) {
            $dir = (string) config('finance.cache_dir', sys_get_temp_dir() . '/opcoes_b3_cache');
            if (!is_dir($dir)) {
                @mkdir($dir, 0747, true);
            }
            self::$cache = new FilesystemAdapter('opcoes_b3', 14400, $dir);
        }
        return self::$cache;
    }

    public static function fetcher(): DataFetcher
    {
        if (self::$fetcher === null) {
            self::$fetcher = new DataFetcher(
                httpClient: new Client([
                    RequestOptions::TIMEOUT       => 10,
                    RequestOptions::CONNECT_TIMEOUT => 5,
                ]),
                requestFactory: new HttpFactory(),
                cache: self::cache()
            );
        }
        return self::$fetcher;
    }

    private static function getFromCache(string $key): ?array
    {
        try {
            $item = self::cache()->getItem($key);
            return $item->isHit() ? ($item->get()) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private static function saveToCache(string $key, array $data, int $ttl = 86400): void
    {
        try {
            $item = self::cache()->getItem($key);
            $item->set($data);
            $item->expiresAfter($ttl);
            self::cache()->save($item);
        } catch (\Throwable) {}
    }

    public static function v8Chart(string $symbol, int $start): array
    {
        try {
            $url = 'https://query1.finance.yahoo.com/v8/finance/chart/' . rawurlencode($symbol)
                 . '?period1=' . $start
                 . '&period2=' . time()
                 . '&interval=1d';

            $cacheKey = 'yahoo_' . md5($symbol);

            if (($cachedData = self::getFromCache($cacheKey)) !== null) {
                return $cachedData;
            }

            $http = new Client([
                RequestOptions::TIMEOUT          => 10,
                RequestOptions::CONNECT_TIMEOUT  => 5,
            ]);
            $response = $http->get($url, [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept'     => 'application/json',
                ],
            ]);

            $payload     = json_decode((string) $response->getBody(), true);
            $payload     = is_array($payload) ? $payload : [];
            $result      = $payload['chart']['result'][0] ?? null;
            if (!is_array($result)) {
                return [];
            }
            $timestamps  = $result['timestamp'] ?? [];
            if (!is_array($timestamps) || $timestamps === []) {
                return [];
            }
            $quote       = $result['indicators'] ?? [];
            $adjclose    = $quote['adjclose'][0]['adjclose'] ?? null;
            $rows = [];
            foreach ($timestamps as $i => $ts) {
                $close =  $quote['quote'][0]['close'][$i] ?? null;
                if ($close === null || !is_numeric((string) $close)) {
                    continue;
                }
                $open  = ($quote['quote'][0]['open'][$i] ?? null);
                $high  = ($quote['quote'][0]['high'][$i] ?? null);
                $low   = ($quote['quote'][0]['low'][$i] ?? null);
                $vol   = ($quote['quote'][0]['volume'][$i] ?? null);
                $adj   = is_array($adjclose)
                    ? ($adjclose[$i] ?? null)
                    : ($adjclose ?? (float) $close);
                $rows[] = [
                    'Date'      => date('Y-m-d', (int) $ts),
                    'Open'      => is_numeric((string) $open) ? (float) $open : null,
                    'High'      => is_numeric((string) $high) ? (float) $high : null,
                    'Low'       => is_numeric((string) $low) ? (float) $low : null,
                    'Close'     => (float) $close,
                    'Adj Close' => is_numeric((string) $adj) ? (float) $adj : (float) $close,
                    'Volume'    => is_numeric((string) $vol) ? (int) $vol : null,
                ];
            }

            self::saveToCache($cacheKey, $rows, 3600);
            return $rows;
        } catch (\Throwable $e) {
            return [];
        }
    }
}
