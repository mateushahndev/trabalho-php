<?php

declare(strict_types=1);

namespace App\Support\Finance;

final class Online
{
    private static ?bool $state = null;

    public static function is(): bool
    {
        if (self::$state !== null) {
            return self::$state;
        }
        self::$state = self::probe();
        return self::$state;
    }

    private static function probe(): bool
    {
        try {
            $items = FetcherFactory::cache()->getItem('online_probe');
            if ($items->isHit() && is_bool($items->get())) {
                return $items->get();
            }
        } catch (\Throwable) {}

        $timeoutMs = (int) config('finance.probe_timeout_ms', 2500);
        $hosts     = config('finance.probe_hosts', []);
        $online    = false;
        foreach ($hosts as $host) {
            if (self::ping($host, $timeoutMs)) {
                $online = true;
                break;
            }
        }

        try {
            $item = FetcherFactory::cache()->getItem('online_probe');
            $item->set($online);
            $item->expiresAfter(300);
            FetcherFactory::cache()->save($item);
        } catch (\Throwable) {}

        return $online;
    }

    private static function ping(string $url, int $timeoutMs): bool
    {
        if (!function_exists('curl_init')) {
            return false;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $timeoutMs);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $timeoutMs);
        curl_setopt($ch, CURLOPT_NOBODY, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'OpcoesB3/1.0 (online probe)');
        curl_exec($ch);
        $errno = curl_errno($ch);
        curl_close($ch);
        return $errno === 0;
    }
}
