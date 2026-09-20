<?php
$transactions = $transactions ?? [];
$filterOp     = $filterOp     ?? '';
?>

<div class="page-header">
    <h1>Transações</h1>
    <a class="btn btn-primary" href="/transacoes/novo">+ Nova transação</a>
</div>
<section class="panel">
    <?php if (empty($transactions)): ?>
        <p class="empty">Nenhuma transação encontrada.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Operação</th>
                        <th>Contrato</th>
                        <th>Vencimento</th>
                        <th class="num">Qtd.</th>
                        <th class="num">Preço</th>
                        <th class="num">Comissão</th>
                        <th class="num">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $t):
                    $total = (float) $t['preco'] * (int) $t['quantidade'] + (float) $t['comissao'];
                    ?>
                        <tr>
                            <td><?= e(br_date($t['data_transacao'])) ?></td>
                            <td><span class="badge badge-<?= strtolower($t['operacao']) ?>"><?= e($t['operacao']) ?></span></td>
                            <td><?= e($t['ativo']) ?> (<?= e($t['tipo_opcao']) ?> R$ <?= e(money((float)$t['preco_exercicio'])) ?>)</td>
                            <td><?= e(br_date($t['data_vencto'])) ?></td>
                            <td class="num"><?= (int) $t['quantidade'] ?></td>
                            <td class="num">R$ <?= money((float) $t['preco'], 2) ?></td>
                            <td class="num"><strong>R$ <?= money((float) $t['comissao'], 2) ?></strong></td>
                            <td class="num">R$ <?= e(money($total, 2)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
