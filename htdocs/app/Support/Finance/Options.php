<?php

declare(strict_types=1);

namespace App\Support\Finance;

use Academe\PhpFinance\Options\BlackScholes;
use DateTimeImmutable;

final class Options
{
    public static function analyze(array $contract, float $spot, float $vol, float $rfr): ?array
    {
        $bs = self::build($contract, $spot, $vol, $rfr);
        if ($bs === null) {
            return null;
        }
        $type = self::type($contract);
        return [
            'type'                 => $type,
            'spot'                 => $spot,
            'strike'               => (float) $contract['preco_exercicio'],
            'expiry_date'          => $contract['data_vencto'] ?? '',
            'time_to_expiry'       => (float) self::expiryFraction($contract['data_vencto'] ?? ''),
            'risk_free_rate'       => $rfr,
            'volatility'           => $vol,
            'theoretical_price'    => $type === 'call' ? $bs->callPrice() : $bs->putPrice(),
            'greeks'               => $bs->getGreeks($type),
            'implied_vol'          => self::impliedVol($contract, $spot, $vol, $rfr),
        ];
    }

    /**
     * @param array{tipo_opcao?:string,preco_exercicio?:float|string,data_vencto?:string} $contract
     */
    public static function build(array $contract, float $spot, float $vol, float $rfr): ?BlackScholes
    {
        $t = self::expiryFraction($contract['data_vencto'] ?? '');
        $k = (float) ($contract['preco_exercicio'] ?? 0);
        if ($spot <= 0 || $k <= 0 || $t < 0) {
            return null;
        }
        try {
            return new BlackScholes(
                spotPrice: $spot,
                strikePrice: $k,
                timeToExpiry: $t,
                riskFreeRate: $rfr,
                volatility: $vol
            );
        } catch (\InvalidArgumentException) {
            return null;
        }
    }

    /** Fraction of a year until expiry */
    public static function expiration(string $dateStr): float
    {
        $now = new DateTimeImmutable('today');
        $exp = DateTimeImmutable::createFromFormat('Y-m-d', $dateStr);
        if ($exp === false) {
            return 0.0;
        }
        $diff = $now->diff($exp);
        return $diff->invert ? 0.0 : $diff->days / 365.25;
    }

    public static function expiryFraction(string $dateStr): float
    {
        return self::expiration($dateStr);
    }

    /**
     * @param array{tipo_opcao?:string,preco_exercicio?:float|string,data_vencto?:string,preco_atual?:float|string} $contract
     * @return float|null
     */
    public static function impliedVol(array $contract, float $spot, float $vol, float $rfr): ?float
    {
        $market = (float) ($contract['preco_atual'] ?? 0);
        if ($market <= 0) {
            return null;
        }
        $t = self::expiryFraction($contract['data_vencto'] ?? '');
        $type = self::type($contract);
        $m = self::build($contract, $spot, $vol, $rfr);
        if ($m === null) {
            return null;
        }
        $intrinsic = $type === 'call'
            ? max(0.0, $spot - (float) $contract['preco_exercicio'])
            : max(0.0, (float) $contract['preco_exercicio'] - $spot);
        if ($market < $intrinsic * 0.99) {
            return null;
        }
        try {
            $iv = $m->impliedVolatility($market, $type, 1e-8, 100);
        } catch (\Throwable) {
            return null;
        }
        if (empty($iv) || $iv < 0.01 || $iv > 4.0) {
            return null;
        }
        try {
            $check = new BlackScholes($spot, (float) $contract['preco_exercicio'], $t, $rfr, $iv);
            $price = $type === 'call' ? $check->callPrice() : $check->putPrice();
            if (abs($price - $market) > max(0.01, 0.025 * $market)) {
                return null;
            }
        } catch (\Throwable) {
            return null;
        }
        return $iv;
    }

    /**
     * @param list<array{ativo:string,tipo_opcao:string,preco_exercicio:float|string,data_vencto:string,quantidade_aberta:int}> $positions
     * @param callable(string): (array{spot:float,vol:float,rfr:float}|null)|null $marketProvider
     * @return array{delta:float,gamma:float,vega:float,theta:float,details:list<array<string,mixed>>,incomplete:int,has_value:bool,total_qtd:int}
     */
    public static function portfolioGreeks(array $positions, ?callable $marketProvider): array
    {
        $total   = ['delta' => 0.0, 'gamma' => 0.0, 'vega' => 0.0, 'theta' => 0.0];
        $details = [];
        $incomplete = 0;
        $qtdTotal = 0;

        foreach ($positions as $p) {
            $qtd = (int) ($p['quantidade_aberta'] ?? 0);
            if ($qtd <= 0) {
                continue;
            }
            $qtdTotal += $qtd;
            $market = $marketProvider !== null ? $marketProvider((string) $p['ativo']) : null;
            if ($market === null) {
                $incomplete++;
                continue;
            }
            $bs = self::build([
                'tipo_opcao'        => $p['tipo_opcao'],
                'preco_exercicio'   => $p['preco_exercicio'],
                'data_vencto'       => $p['data_vencto'],
            ], $market['spot'], $market['vol'], $market['rfr']);
            if ($bs === null) {
                $incomplete++;
                continue;
            }
            $g = $bs->getGreeks(self::type($p));
            $total['delta']  += $g['delta']  * $qtd;
            $total['gamma']  += $g['gamma']  * $qtd;
            $total['vega']   += $g['vega']   * $qtd;
            $total['theta']  += $g['theta']  * $qtd;
            $details[] = [
                'ativo'         => (string) $p['ativo'],
                'tipo_opcao'    => (string) $p['tipo_opcao'],
                'qtd'           => $qtd,
                'spot'          => $market['spot'],
                'vol'           => round($market['vol'] * 100, 2) . '%',
                'delta'         => round($g['delta'] * $qtd, 2),
                'gamma'         => round($g['gamma'] * $qtd, 2),
                'vega'          => round($g['vega']  * $qtd, 2),
                'theta'         => round($g['theta'] * $qtd, 2),
            ];
        }

        return [
            'delta'      => round($total['delta'], 2),
            'gamma'      => round($total['gamma'], 4),
            'vega'       => round($total['vega'], 2),
            'theta'      => round($total['theta'], 2),
            'details'    => $details,
            'incomplete' => $incomplete,
            'has_value'  => !empty($details),
            'total_qtd'  => $qtdTotal,
        ];
    }

    private static function type(array $contract): string
    {
        return strtolower((string) $contract['tipo_opcao']) === 'put' ? 'put' : 'call';
    }
}
