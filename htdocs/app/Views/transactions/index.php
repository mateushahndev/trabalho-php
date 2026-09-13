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
                        <th class="num">Qtd.</th>
                        <th class="num">Preço</th>
                        <th class="num">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $t): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($t['data_transacao'])) ?></td>
                            <td><span class="badge badge-<?= strtolower($t['operacao']) ?>"><?= $t['operacao'] ?></span></td>
                            <td><?= $t['ativo'] ?> (<?= $t['tipo_opcao'] ?>)</td>
                            <td class="num"><?= $t['quantidade'] ?></td>
                            <td class="num">R$ <?= number_format($t['preco'], 4, ',', '.') ?></td>
                            <td class="num"><strong>R$ <?= number_format($t['preco'] * $t['quantidade'] + $t['comissao'], 2, ',', '.') ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>