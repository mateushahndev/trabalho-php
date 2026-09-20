<?php
$positions = $positions ?? [];
$filter    = $filter    ?? '';
?>

<div class="page-header">
    <h1>Posições</h1>
    <a class="btn btn-primary" href="/posicoes/novo">Nova posição</a>
</div>
<section class="panel">
    <?php if (empty($positions)): ?>
        <p class="empty">Nenhuma posição encontrada.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Contrato</th>
                        <th>Tipo</th>
                        <th class="num">Strike</th>
                        <th>Vencimento</th>
                        <th class="num">Qtd. Total</th>
                        <th class="num">Qtd. Aberta</th>
                        <th class="num">Preço médio</th>
                        <th>Data de Abertura</th>
                        <th>Status</th>
                        <th class="num">Resultado</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($positions as $p):
                        $qtdTotal = (int) $p['quantidade_total'];
                        $qtdAberta = (int) $p['quantidade_aberta'];
                        $pnl = $p['status'] === 'ABERTA' ? ((float) $p['preco_mercado'] - (float) $p['preco_medio']) * $qtdAberta : 0.0;
                    ?>
                        <tr>
                            <td><strong><?= e($p['ativo']) ?></strong></td>
                            <td><span class="badge badge-<?= strtolower((string) $p['tipo_opcao']) ?>"><?= e($p['tipo_opcao']) ?></span></td>
                            <td class="num">R$ <?= e(money((float) $p['preco_exercicio'])) ?></td>
                            <td><?= e(br_date($p['data_vencto'])) ?></td>
                            <td class="num"><?= $qtdTotal ?></td>
                            <td class="num"><?= $qtdAberta ?>/<?= $qtdTotal ?></td>
                            <td class="num">R$ <?= e(money((float) $p['preco_medio'], 2)) ?></td>
                            <td><?= e(br_date($p['data_abertura'])) ?></td>
                            <td>
                                <?php if ($p['status'] === 'ABERTA'): ?>
                                    <span class="badge badge-aberta">Aberta</span>
                                <?php else: ?>
                                    <span class="badge badge-fechada">Fechada</span>
                                <?php endif; ?>
                            </td>
                            <td class="num <?= $p['status'] === 'ABERTA' ? ($pnl >= 0 ? 'text-up' : 'text-down') : '' ?>">
                                <?php if ($p['status'] === 'FECHADA'): ?>-
                                <?php else: ?><?= $pnl >= 0 ? '+' : '-' ?>R$ <?= e(money(abs($pnl))) ?>
                                <?php endif; ?>
			    </td>
                            <td>
                                <?php if ($p['status'] === 'ABERTA'): ?>
                                    <a class="btn btn-ghost btn-sm" href="/posicoes/<?= $p['id'] ?>/fechar">Fechar</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
