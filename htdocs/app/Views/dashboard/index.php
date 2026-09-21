<?php
$positions = $positions ?? [];
$tx        = $transactions ?? [];
?>
<div class="page-header">
    <h1>Dashboard</h1>
</div>
<div class="stats-grid">
    <div class="stat-card">
        <span class="stat-label">Posições abertas</span>
        <span class="stat-value"><?= $stats['positions'] ?></span>
    </div>
    <div class="stat-card">
        <span class="stat-label">Quantia investida</span>
        <span class="stat-value">R$ <?= e(money($stats['invested'])) ?></span>
    </div>
    <div class="stat-card">
        <span class="stat-label">Resultado não realizado</span>
        <span class="stat-value <?= $stats['pnl'] >= 0 ? 'text-up' : 'text-down' ?>">
        <?= $stats['pnl'] >= 0 ? '+' : '-' ?>R$ <?= e(money(abs($stats['pnl']))) ?>
        </span>
    </div>
    <div class="stat-card">
        <span class="stat-label">Transações</span>
        <span class="stat-value"><?= $stats['trades'] ?></span>
    </div>
</div>
<section class="panel">
    <div class="panel-header">
        <h2>Posições ativas</h2>
        <a class="btn btn-primary btn-sm" href="/posicoes/novo">+ Nova posição</a>
    </div>
    <?php if ($positions === []): ?>
    <p class="empty">Nenhuma posição aberta.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Ativo</th>
                    <th>Tipo</th>
                    <th class="num">Strike</th>
		    <th>Vencimento</th>
                    <th class="num">Qtd.</th>
                    <th class="num">Preço médio</th>
                    <th class="num">Preço de Mercado</th>
                    <th class="num">Resultado</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($positions as $p):
                $qtd   = (int) $p['quantidade_aberta'];
                $pnl   = ((float)$p['preco_mercado'] - (float)$p['preco_medio']) * $qtd;
            ?>
                <tr>
                    <td><strong><?= e($p['ativo']) ?></strong></td>
                    <td><span class="badge badge-<?= strtolower((string) $p['tipo_opcao']) ?>"><?= e($p['tipo_opcao']) ?></span></td>
                    <td class="num">R$ <?= e(money((float)$p['preco_exercicio'])) ?></td>
                    <td><?= e(br_date($p['data_vencto'])) ?></td>
                    <td class="num"><?= $qtd ?></td>
                    <td class="num">R$ <?= e(money((float)$p['preco_medio'], 2)) ?></td>
                    <td class="num">R$ <?= e(money((float)$p['preco_mercado'], 2)) ?></td>
                    <td class="num <?= $pnl >= 0 ? 'text-up' : 'text-down' ?>">
                    <?= $pnl >= 0 ? '+' : '-' ?>R$ <?= e(money(abs($pnl))) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>

<section class="panel">
    <div class="panel-header">
        <h2>Transações recentes</h2>
        <a class="btn btn-ghost btn-sm" href="/transacoes">Ver todas</a>
    </div>

    <?php if ($tx === []): ?>
    <p class="empty">Nenhuma transação registrada ainda. <a href="/transacoes/novo">Registrar uma transação</a>.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Data/hora</th>
                    <th>Operação</th>
                    <th>Contrato</th>
                    <th class="num">Qtd.</th>
                    <th class="num">Preço</th>
                    <th class="num">Total</th>
                    </tr>
            </thead>
            <tbody>
            <?php foreach ($tx as $t): ?>
                <tr>
                    <td><?= e(br_date($t['data_transacao'])) ?></td>
                    <td><span class="badge badge-<?= strtolower((string) $t['operacao']) ?>"><?= e($t['operacao']) ?></span></td>
                    <td><?= e($t['ativo']) ?> <span class="muted">(<?= e($t['tipo_opcao']) ?>)</span></td>
                    <td class="num"><?= (int) $t['quantidade'] ?></td>
                    <td class="num">R$ <?= e(money((float) $t['preco'], 2)) ?></td>
                    <td class="num">R$ <?= e(money((float) $t['preco'] * (int) $t['quantidade'] + (float) $t['comissao'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>
