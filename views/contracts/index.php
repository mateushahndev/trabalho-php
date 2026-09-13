<div class="page-header">
    <h1>Contratos</h1>
    <a class="btn btn-primary" href="/contratos/novo">+ Novo contrato</a>
</div>
<section class="panel">
    <?php if (empty($contracts)): ?>
        <p class="empty">Nenhum contrato cadastrado.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Ativo</th>
                        <th>Tipo</th>
                        <th class="num">Strike</th>
                        <th>Vencimento</th>
                        <th class="num">Preço atual</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contracts as $c): ?>
                        <tr>
                            <td><strong><?= $c['ativo'] ?></strong></td>
                            <td><span class="badge badge-<?= strtolower($c['tipo_opcao']) ?>"><?= $c['tipo_opcao'] ?></span></td>
                            <td class="num">R$ <?= number_format($c['preco_exercicio'], 2, ',', '.') ?></td>
                            <td><?= date('d/m/Y', strtotime($c['data_vencto'])) ?></td>
                            <td class="num">R$ <?= number_format($c['preco_atual'], 4, ',', '.') ?></td>
                            <td><a class="btn btn-ghost btn-sm" href="/contratos/<?= $c['id'] ?>/editar">Editar</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>