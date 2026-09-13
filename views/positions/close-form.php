<div class="page-header">
    <h1>Fechar posição</h1>
    <small><a href="/posicoes" class="muted">← Voltar</a></small>
</div>
<div class="info-box">
    <strong>Resumo:</strong>
    <?= $position['ativo'] ?> <?= $position['tipo_opcao'] ?>
    R$ <?= number_format($position['preco_exercicio'], 2, ',', '.') ?>
    &mdash; <?= $position['quantidade_aberta'] ?> contratos abertos
</div>
<form class="form form-card" method="post" action="/posicoes/<?= $position['id'] ?>/fechar" novalidate>
    <div class="form-group">
        <label for="quantidade">Quantidade a fechar *</label>
        <input type="number" id="quantidade" name="quantidade">
        <small class="muted">Máximo: <?= $position['quantidade_aberta'] ?></small>
    </div>
    <div class="form-group">
        <label for="preco">Preço de venda *</label>
        <input type="number" step="0.0001" id="preco" name="preco">
    </div>
    <button type="submit" class="btn btn-primary">Fechar posição</button>
</form>