<div class="page-header">
    <h1>Análise &amp; Risco</h1>
    <div>
        <?php if ($online): ?>
            <span class="badge badge-aberta">Conectado</span>
        <?php else: ?>
            <span class="badge badge-fechada">Offline</span>
        <?php endif; ?>
        <small class="muted">RF: <?= e($rfr) ?> (<?= e($rfr_label) ?>)</small>
    </div>
</div>

<section class="panel">
    <div class="panel-header">
        <h2>Greeks da Carteira</h2>
        <small class="">Agregados das posições abertas (via Black-Scholes)</small>
    </div>
    <?php if ($greeks === null || ($greeks !== [] && empty($greeks['has_value'])) || !$online): ?>
        <p class="empty"><?= $online ? 'Sem dados suficientes para calcular gregas (verifique posições abertas e conectividade).' : 'Conecte-se para ver gregas.' ?></p>
    <?php else:
        $g = $greeks;
    ?>
    <div class="kv-table">
        <div class="stat-card">
            <span class="stat-label">Delta</span>
            <span class="stat-value"><?= e($g['delta']) ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Gamma</span>
            <span class="stat-value"><?= e($g['gamma']) ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Vega</span>
            <span class="stat-value"><?= e($g['vega']) ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Theta</span>
            <span class="stat-value"><?= e($g['theta']) ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Contratos</span>
            <span class="stat-value"><?= e((string)$g['total_qtd']) ?></span>
        </div>
    </div>
    <?php if ($g['details'] !== []): ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Ativo</th>
                        <th>Tipo</th>
                        <th class="num">Qtd</th>
                        <th class="num">Spot</th>
                        <th class="num">Vol</th>
                        <th class="num">Delta</th>
                        <th class="num">Gamma</th>
                        <th class="num">Vega</th>
                        <th class="num">Theta</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($g['details'] as $d): ?>
                        <tr>
                            <td><strong><?= e($d['ativo']) ?></strong></td>
                            <td><span class="badge badge-<?= strtolower($d['tipo_opcao']) ?>"><?= e($d['tipo_opcao']) ?></span></td>
                            <td class="num"><?= e((string)$d['qtd']) ?></td>
                            <td class="num">R$ <?= e(money($d['spot'] ?? 0)) ?></td>
                            <td class="num"><?= e($d['vol']) ?></td>
                            <td class="num"><?= e($d['delta']) ?></td>
                            <td class="num"><?= e($d['gamma']) ?></td>
                            <td class="num"><?= e($d['vega']) ?></td>
                            <td class="num"><?= e($d['theta']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    <?php if (($g['incomplete'] ?? 0) > 0): ?>
        <small class="muted">&nbsp;⚠️ <?= $g['incomplete'] ?> ativo(s) sem dados de mercado.</small>
    <?php endif; ?>
    <?php endif; ?>
</section>

<section class="panel">
    <div class="panel-header">
        <h2>Métricas de Trade Realizado</h2>
        <small class="muted">Retornos de vendas finalizadas</small>
    </div>
    <?php if ($tradeMetrics === null): ?>
        <p class="empty">Histórico insuficiente (menor que 2 vendas). Cadastre vendas para ver métricas.</p>
    <?php else:
        $tm = $tradeMetrics;
    ?>
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-label">Vendas</span>
            <span class="stat-value"><?= e((string)$tm['count']) ?></span>
            <span class="stat-label">P&amp;L Total</span>
            <span class="stat-value <?= $tm['total_pnl'] >= 0 ? 'text-up' : 'text-down' ?>">
                <?= $tm['total_pnl'] >= 0 ? '+' : '−' ?>R$ <?= e(money(abs($tm['total_pnl']))) ?>
            </span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Taxa de Acerto</span>
            <span class="stat-value"><?= e((string)$tm['win_rate']) ?>%</span>
            <?php if ($tm['kelly'] !== null): ?>
            <span class="stat-label">Kelly</span>
            <span class="stat-value <?= $tm['kelly'] > 0 ? 'text-up' : 'text-down' ?>">
                <?= $tm['kelly'] > 0 ? '+' : '' ?><?= e(round($tm['kelly'], 1)) ?>%
            </span>
            <?php endif; ?>
        </div>
        <div class="stat-card">
            <span class="stat-label">Sharpe</span>
            <span class="stat-value"><?= e($tm['sharpe']) ?></span>
            <span class="stat-label">Sortino</span>
            <span class="stat-value"><?= e($tm['sortino']) ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Max DD</span>
            <span class="stat-value text-down"><?= e(round($tm['max_drawdown'], 2)) ?>%</span>
            <span class="stat-label">VaR 95%</span>
            <span class="stat-value text-down"><?= e(round($tm['var95'], 2)) ?>%</span>
        </div>
    </div>
    <?php if ($tm['kelly_note'] !== null): ?>
        <small class="muted">ℹ️ <?= e($tm['kelly_note']) ?></small>
    <?php endif; ?>
    <?php endif; ?>
</section>

<section class="panel">
    <div class="panel-header">
        <h2>Benchmark — Beta & Alpha (vs Ibovespa)</h2>
        <small class="muted">OLS simples sobre retornos diários</small>
    </div>
    <?php if ($benchmark === null || !$online): ?>
        <p class="empty"><?= $online ? 'Dados insuficientes para regressão.' : 'Conecte-se para ver benchmark.' ?></p>
    <?php else:
        $b = $benchmark;
    ?>
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-label">Beta</span>
            <span class="stat-value"><?= e($b['beta']) ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Alpha (anualizado)</span>
            <span class="stat-value <?= $b['alpha'] >= 0 ? 'text-up' : 'text-down' ?>">
                <?= $b['alpha'] >= 0 ? '+' : '−' ?><?= e(round($b['alpha'] * 100, 2)) ?>%
            </span>
        </div>
        <div class="stat-card">
            <span class="stat-label">R²</span>
            <span class="stat-value"><?= e($b['r_squared']) ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label">p-Value</span>
            <span class="stat-value <?= (float)$b['p_value'] < 0.05 ? 'text-up' : 'text-down' ?>">
                <?= e((string)$b['p_value']) ?>
            </span>
        </div>
    </div>
    <small class="muted">&nbsp;Observações: <code><?= e((string)$b['n']) ?></code></small>
    <?php endif; ?>
</section>

<section class="panel">
    <div class="panel-header">
        <h2>Rolling Beta (Janela Móvel)</h2>
        <small class="muted">Beta em janelas de <?= $rollingBeta ? e((string)($rollingBeta['window_size'] ?: 21)) : '-' ?> dias</small>
    </div>
    <?php if ($rollingBeta === null || !$online): ?>
        <p class="empty"><?= $online ? 'Dados insuficientes para rolling.' : 'Conecte-se.' ?></p>
    <?php else:
        $rb = $rollingBeta;
    ?>
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-label">Beta Inicial</span>
            <span class="stat-value"><?= e($rb['first_beta']) ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Beta Final</span>
            <span class="stat-value"><?= e($rb['last_beta']) ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Beta Médio</span>
            <span class="stat-value"><?= e($rb['avg_beta']) ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Desvio Beta</span>
            <span class="stat-value"><?= e(round($rb['std_beta'], 3)) ?></span>
        </div>
    </div>
    <small class="muted">&nbsp;Janelas: <code><?= e((string)$rb['windows']) ?></code> | R² médio: <code><?= e(round($rb['avg_r_squared'], 4)) ?></code></small>
    <?php endif; ?>
</section>

<section class="panel">
    <div class="panel-header">
        <h2>Modelo Fator 5-Fator (Fama-French Diário)</h2>
        <small class="muted">Mkt-RF, SMB, HML, RMW, CMA</small>
    </div>
    <?php if ($factor5 === null || ($factor5['zip_unavailable'] ?? false)): ?>
        <p class="empty"><?= $factor5['zip_note'] ?? 'Fama-French diário indisponível (ext-zip ausente ou sem conexão).'?></p>
    <?php elseif (($factor5['ff_note'] ?? null) !== null && $factor5['ff_note'] !== ''): ?>
        <p class="empty"><?= e($factor5['ff_note']) ?></p>
    <?php elseif (empty($factor5['loadings'])): ?>
        <p class="empty">Dados insuficientes para regressão multivariada.</p>
    <?php else:
        $loadings = $factor5['loadings'];
        $pvalues  = $factor5['p_values'] ?? [];
    ?>
    <small class="muted">&nbsp;N: <code><?= e((string)$factor5['n']) ?></code> | R²: <code><?= e((string)round($factor5['r_squared'], 4)) ?></code> | R² Ajust: <code><?= e((string)round($factor5['adj_r_squared'], 4)) ?></code></small>
    <div class="table-wrap" style="margin-top:1rem;">
        <table>
            <thead>
                <tr>
                    <th>Fator</th>
                    <th class="num">Loading (β)</th>
                    <th class="num">R²</th>
                    <th class="num">p-Value</th>
                    <th class="num">Significância</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($loadings as $name => $loading):
                    $pval = $pvalues[$name] ?? null;
                    $sig  = match (true) {
                        is_null($pval) || $pval >= 1.0 => '',
                        $pval < 0.01 => '***',
                        $pval < 0.05 => '**',
                        $pval < 0.10 => '*',
                        default => '',
                    };
                ?>
                <tr>
                    <td><strong><?= e($name) ?></strong></td>
                    <td class="num"><?= e($loading) ?></td>
                    <td class="num"><?= e(round($factor5['r_squared'], 4)) ?></td>
                    <td class="num"><?= $pval !== null ? e(round($pval, 4)) : '—' ?></td>
                    <td class="num"><?= $sig !== '' ? '<span class="badge badge-aberta">' . e($sig) . '</span>' : '—' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>
