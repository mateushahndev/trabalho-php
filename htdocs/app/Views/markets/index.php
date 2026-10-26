<div class="page-header">
    <h1>Mercados &amp; Estatísticas</h1>
    <div>
        <?php if ($online): ?>
            <span class="badge badge-aberta">Conectado</span>
            <small class="muted">RF: <?= e($rfr_label) ?> (<?= e($rfr) ?>)</small>
        <?php else: ?>
            <span class="badge badge-fechada">Offline</span>
            <small class="muted">— Preços teóricos indisponíveis.</small>
        <?php endif; ?>
    </div>
</div>

<?php if ($online): ?>
    <?php foreach ($underlyings as $u):
        $hasData = count($u['closes']) >= 5 && $u['stats'] !== null;
    ?>
        <section class="panel">
            <div class="panel-header">
                <h2><?= e($u['ativo']) ?> <small class="muted">(<?= e($u['symbol']) ?>)</small></h2>
                <span>Spot: <strong>R$ <?= $u['spot'] !== null ? e(money($u['spot'])) : '—' ?></strong></span>
            </div>

            <?php if (!$hasData): ?>
                <p class="empty"><?= count($u['closes']) >= 20 ? 'Dados insuficientes para estatísticas completas.' : 'Sem dados suficientes de mercado para ' . e($u['ativo']) ?>.</p>
            <?php else:
                $s = $u['stats'];
            ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <span class="stat-label">Retorno Acumulado</span>
                    <span class="stat-value <?= $s['cumulative_return'] >= 0 ? 'text-up' : 'text-down' ?>">
                        <?= $s['cumulative_return'] >= 0 ? '+' : '' ?><?= e(round($s['cumulative_return'] * 100, 2)) ?>%
                    </span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Retorno Anualizado</span>
                    <span class="stat-value <?= $s['annualized_return'] >= 0 ? 'text-up' : 'text-down' ?>">
                        <?= $s['annualized_return'] >= 0 ? '+' : '' ?><?= e(round($s['annualized_return'] * 100, 2)) ?>%
                    </span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Volatilidade (σ)</span>
                    <span class="stat-value"><?= e(round($s['volatility'] * 100, 2)) ?>%</span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Sharpe</span>
                    <span class="stat-value"><?= e(round($s['sharpe'], 2)) ?></span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Sortino</span>
                    <span class="stat-value"><?= e(round($s['sortino'], 2)) ?></span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Max Drawdown</span>
                    <span class="stat-value text-down"><?= e(round($s['max_drawdown'] * 100, 2)) ?>%</span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">VaR (95%)</span>
                    <span class="stat-value text-down"><?= e(round($s['var95'] * 100, 2)) ?>%</span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">CVaR (95%)</span>
                    <span class="stat-value text-down"><?= e(round($s['cvar95'] * 100, 2)) ?>%</span>
                </div>
            </div>

            <div class="panel-header" style="margin-top:1rem;">
                <h2>Gráfico de Preço</h2>
                <small class="muted"><?= count($u['closes']) ?> dias</small>
            </div>
            <canvas class="price-chart" width="1000" height="250" data-closes='<?= json_encode(array_slice(array_values($u['closes']), -252, 252)) ?>'></canvas>
            <?php endif; ?>
        </section>
    <?php endforeach; ?>

    <?php
    $labels    = ['ativo', 'ativos'];
    $pagerHref = static fn (int $p): string => '/mercados?page=' . $p;
    include __DIR__ . '/../partials/pager.php';
    ?>
<?php else: ?>
    <section class="panel">
        <p class="empty">Sem conexão com a internet. Para ver dados de mercado e estatísticas, <strong>conecte-se à Internet</strong>.</p>
    </section>
<?php endif; ?>
