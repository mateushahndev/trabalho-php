<?php

declare(strict_types=1);

namespace App\Support\Finance;

use Academe\PhpFinance\General\Utils;
use Academe\PhpFinance\Returns\TSeries;
use Academe\PhpFinance\Statistics\OLS;
use Academe\PhpFinance\Statistics\RollingOLS;

use App\Core\Model;

class Analysis extends Model
{
    public static function realizedSales(int $userId): array
    {
        $pdo = self::db();
        $stmt = $pdo->prepare(
            "SELECT t.data_transacao, t.preco as preco_venda, t.quantidade,"
            . " p.preco_medio as preco_medio, c.ativo"
            . " FROM transacoes t"
            . " JOIN contratos  c ON c.id = t.contrato_id"
            . " JOIN posicoes p ON p.contrato_id = t.contrato_id"
            . " AND p.status = 'FECHADA' AND p.usuario_id = t.usuario_id"
            . " WHERE t.usuario_id = :uid AND t.operacao = 'VENDA'"
            . " ORDER BY t.data_transacao ASC, t.id ASC"
        );
        $stmt->execute(['uid' => $userId]);
        $result = [];
        foreach ($stmt->fetchAll() as $r) {
            $precoVenda = (float) $r['preco_venda'];
            $media      = (float) $r['preco_medio'];
            if ($media <= 0) {
                continue;
            }
            $qtd       = (int) $r['quantidade'];
            $pct       = $precoVenda / $media - 1.0;
            $gross     = $pct * $qtd * $media;
            $result[]  = [
                'date'       => (string) $r['data_transacao'],
                'pnl_pct'    => $pct,
                'gross'      => round($gross, 2),
                'ativo'      => (string) $r['ativo'],
                'venda_preco'=> $precoVenda,
                'media'      => $media,
            ];
        }
        return $result;
    }

    public static function tradeMetrics(int $userId): ?array
    {
        $sales = self::realizedSales($userId);
        if (count($sales) < 2) {
            return null;
        }

        $returns = array_map(fn($s) => $s['pnl_pct'], $sales);
        $series  = new TSeries(array_values($returns), array_keys($returns), 'D');
        $rfr     = ReferenceRates::riskFreeRate()[0];

        $total   = round(array_sum(array_map(fn($s) => $s['gross'], $sales)), 2);
        $wins    = count(array_filter($sales, fn($s) => $s['pnl_pct'] > 0));
        $losses  = count($sales) - $wins;
        $winRate = $wins / count($sales);

        $winGrosses = array_map(fn($s) => $s['gross'],
            array_filter($sales, fn($s) => $s['gross'] > 0));
        $lossGrosses = array_map(fn($s) => abs($s['gross']),
            array_filter($sales, fn($s) => $s['gross'] <= 0));

        $avgWin  = ($wins > 0 && $winGrosses !== []) ? array_sum($winGrosses) / $wins : 0.01;
        $avgLoss = ($losses > 0 && $lossGrosses !== []) ? array_sum($lossGrosses) / $losses : 0.01;

        $kelly   = ($winRate < 1.0 && $avgLoss > 0)
            ? Utils::kellyFormula($winRate, max($avgWin, 0.01), max($avgLoss, 0.01))
            : null;
        $kellyPct = $kelly !== null ? round($kelly * 100, 1) : null;

        return [
            'count'         => count($sales),
            'total_pnl'     => $total,
            'wins'          => $wins,
            'losses'        => $losses,
            'win_rate'      => round($winRate * 100, 1),
            'avg_win_pct'   => round($avgWin * 100, 2),
            'avg_loss_pct'  => round($avgLoss * 100, 2),
            'sharpe'        => round($series->sharpeRatio($rfr), 3),
            'sortino'       => round($series->sortino(), 3),
            'calmar'        => round(Utils::calmarRatio($returns, 252), 3),
            'max_drawdown'  => round($series->maxDrawdown() * 100, 2),
            'var95'         => round(Utils::valueAtRisk($returns, 0.95) * 100, 2),
            'cvar95'        => round(Utils::conditionalValueAtRisk($returns, 0.95) * 100, 2),
            'kelly'         => $kellyPct,
            'kelly_note'    => $kellyPct !== null
                ? ($kellyPct > 0
                    ? "Kelly sugere apostar {$kellyPct}% do capital"
                    : 'Kelly negativo — evite alavancagem')
                : 'Dados insuficientes (>= 2 trades necessários)',
        ];
    }

    public static function portfolioDailyReturns(int $userId, int $days = 252): ?array
    {
        $stmt = self::db()->prepare(
            "SELECT c.ativo"
            . " FROM posicoes p"
            . " JOIN contratos c ON c.id = p.contrato_id"
            . " WHERE p.usuario_id = :uid"
            . "   AND p.status = 'ABERTA'"
            . "   AND p.quantidade_aberta > 0"
            . " ORDER BY p.quantidade_aberta DESC"
        );
        $stmt->execute(['uid' => $userId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        if ($rows === []) {
            return null;
        }

        $closes  = MarketData::closes((string) $rows[0], $days);
        $returns = MarketData::returns($closes);
        return $returns !== [] ? $returns : null;
    }

    public static function benchmark(int $userId, int $days = 252): ?array
    {
        $returns = self::portfolioDailyReturns($userId, $days);
        if ($returns === null || count($returns) < 30) {
            return null;
        }
        $benchCloses = MarketData::closes(config('finance.benchmark_b3', 'IBOV'), $days);
        if ($benchCloses === [] || count($benchCloses) < 30) {
            return null;
        }

        $benchRet = MarketData::returns($benchCloses);

        $intersect = [];
        foreach ($returns as $d => $r) {
            if (isset($benchRet[$d])) {
                $intersect[$d] = [$r, $benchRet[$d]];
            }
        }
        if (count($intersect) < 30) {
            return null;
        }

        $y = array_column($intersect, 0);
        $X = array_column($intersect, 1);

        try {
            $ols = new OLS($y, $X, true);
        } catch (\InvalidArgumentException) {
            return null;
        }

        $cfs = $ols->getCoefficients();

        return [
            'beta'      => round($cfs[1] ?? 0, 3),
            'alpha'     => round(($cfs[0] ?? 0) * 252, 5),
            'r_squared' => round($ols->getRSquared(), 4),
            'p_value'   => round($ols->getPValues()[1] ?? 1.0, 4),
            'n'         => count($y),
        ];
    }

    public static function rollingBeta(int $userId, int $days = 252, int $window = 21): ?array
    {
        $returns = self::portfolioDailyReturns($userId, $days);
        if ($returns === null || count($returns) < $window + 10) {
            return null;
        }

        $benchCloses = MarketData::closes(config('finance.benchmark_b3', 'IBOV'), $days);
        if ($benchCloses === [] || count($benchCloses) < $window + 10) {
            return null;
        }

        $benchRet  = MarketData::returns($benchCloses);
        $intersect = [];
        foreach ($returns as $d => $r) {
            if (isset($benchRet[$d])) {
                $intersect[$d] = [$r, $benchRet[$d]];
            }
        }
        if (count($intersect) < $window + 2) {
            return null;
        }

        $dates   = array_keys($intersect);
        $y       = array_column($intersect, 0);
        $X       = array_map(fn($v) => [$v[1]], $intersect);

        try {
            $rolling = new RollingOLS($y, $X, $window, true);
        } catch (\InvalidArgumentException) {
            return null;
        }

        $betaSeries     = $rolling->getCoefficientSeries(1);
        $rSquared       = $rolling->getRSquared();

        $count  = count($betaSeries);
        if ($count === 0) {
            return null;
        }

        $firstBeta = $betaSeries[0] ?? 0;
        $lastBeta  = end($betaSeries);
        $avgBeta   = array_sum($betaSeries) / $count;
        if (count($betaSeries) > 1) {
            $mean = $avgBeta;
            $sumSq = array_reduce($betaSeries, static fn($carry, $b) => $carry + ($b - $mean) ** 2, 0);
            $stdBeta = sqrt($sumSq / (count($betaSeries) - 1));
        } else {
            $stdBeta = 0;
        }

        $avgRs = $rSquared !== []
            ? array_sum($rSquared) / count($rSquared)
            : 0;

        return [
            'first_beta'    => round($firstBeta, 3),
            'last_beta'     => round($lastBeta, 3),
            'avg_beta'      => round($avgBeta, 3),
            'std_beta'      => round($stdBeta, 3),
            'avg_r_squared' => round($avgRs, 4),
            'windows'       => $count,
            'window_size'   => $window,
        ];
    }

    public static function factorModel5F(int $userId, int $days = 126): ?array
    {
        if (!class_exists('ZipArchive')) {
            return ['zip_unavailable' => true, 'zip_note' => 'ext-zip ausente — Fama-French diário indisponível'];
        }

        $returns = self::portfolioDailyReturns($userId, $days);
        if ($returns === null) {
            return ['returns_unavailable' => true, 'returns_note' => 'sem retornos de carteira (posições abertas insuficientes)'];
        }

        try {
            $ff = FetcherFactory::fetcher()->getFamaFrench('F-F_Research_Data_5_Factors_2x3_daily');
        } catch (\Throwable $e) {
            return ['ff_info' => $e->getMessage()];
        }

        if ($ff === []) {
            return ['ff_empty' => true, 'ff_note' => 'dados Fama-French retornado vazio'];
        }

        $ffDates  = [];
        $ffValues = [];
        foreach ($ff as $row) {
            $dateStr = (string) ($row['date'] ?? '');
            if ($dateStr === '') {
                continue;
            }
            $values = $row['values'] ?? [];
            if (!is_array($values) || count($values) < 5) {
                continue;
            }
            preg_match('/(\\d{4})(\\d{2})(\\d{2})/', $dateStr, $m);
            $dateStr = $m[1] . '-' . $m[2] . '-' . $m[3];
            $v = [];
            foreach (array_slice($values, 0, 5) as $val) {
                $num = (float) $val;
                if (!is_finite($num)) {
                    continue 2;
                }
                $v[] = $num / 100.0;
            }
            if (count($v) < 5) {
                continue;
            }
            $ffDates[]  = $dateStr;
            $ffValues[] = $v;
        }

        if (count($ffValues) < min(30, $days / 30)) {
            return ['ff_rows_insufficient' => count($ffValues), 'ff_note' => 'linhas Fama-French insuficientes para regressão'];
        }

        $intersect = [];
        foreach ($ffValues as $i => $frow) {
            $d = $ffDates[$i] ?? '';
            if (isset($returns[$d])) {
                $intersect[$d] = [$returns[$d], $frow];
            }
        }
        if (count($intersect) < 30) {
            return ['aligned_count' => count($intersect), 'ff_note' => 'poucas datas alinhadas (mín. 30) para regressão'];
        }
        $y = [];
        $X = [];
        foreach ($intersect as $_d => $pair) {
            $y[]    = $pair[0];
            $X[]    = $pair[1]; 
        }
        $n = count($y);

        try {
            $ols = new OLS($y, $X, true);
        } catch (\InvalidArgumentException $e) {
            return ['ols_error' => $e->getMessage(), 'n' => $n];
        }

        $cfs = $ols->getCoefficients(); 

        $labels = ['alpha (intercept)', 'Mkt-RF', 'SMB', 'HML', 'RMW', 'CMA'];
        $pvals  = $ols->getPValues();

        return [
            'n'            => $n,
            'r_squared'    => round($ols->getRSquared(), 4),
            'adj_r_squared'=> round($ols->getAdjustedRSquared() ?? 0, 4),
            'loadings'     => array_combine($labels, array_map(static fn($c) => round($c, 5), $cfs)),
            'p_values'     => array_combine($labels, array_map(static fn($p) => round($p, 4), $pvals)),
            'ff_note'      => null,
        ];
    }
}
