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
                        <th>Ativo</th>
                        <th>Tipo</th>
                        <th class="num">Qtd. aberta</th>
                        <th class="num">Preço médio</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($positions as $p): ?>
                        <tr>
                            <td><strong><?= $p['ativo'] ?></strong></td>
                            <td><span class="badge badge-<?= strtolower($p['tipo_opcao']) ?>"><?= $p['tipo_opcao'] ?></span></td>
                            <td class="num"><?= $p['quantidade_aberta'] ?>/<?= $p['quantidade_total'] ?></td>
                            <td class="num">R$ <?= number_format($p['preco_medio'], 2, ',', '.') ?></td>
                            <td>
                                <?php if ($p['status'] === 'ABERTA'): ?>
                                    <span class="badge badge-aberta">Aberta</span>
                                <?php else: ?>
                                    <span class="badge badge-fechada">Fechada</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($p['status'] === 'ABERTA'): ?>
                                    <a class="btn btn-ghost btn-sm" href="/posicoes/<?= $p['id'] ?>/fechar">Fechar</a>
                                <?php else: ?>
                                    &mdash;
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
