<div class="page-header">
    <h1>Nova posição</h1>
    <small><a href="/posicoes" class="muted">Voltar</a></small>
</div>
<form class="form form-card" method="post" action="/posicoes" novalidate>
    <div class="form-group">
        <label for="contrato_id">Contrato *</label>
        <select id="contrato_id" name="contrato_id">
            <option value="">Selecione...</option>
            <?php foreach($contracts as $c): ?>
                <option value="<?= $c['id'] ?>">
                    <?= $c['ativo'] ?> <?= $c['tipo_opcao'] ?> R$ <?= number_format($c['preco_exercicio'], 2, ',', '.') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label for="quantidade">Quantidade *</label>
            <input type="number" id="quantidade" name="quantidade">
        </div>
        <div class="form-group">
            <label for="preco_medio">Preço médio *</label>
            <input type="number" step="0.0001" id="preco_medio" name="preco_medio">
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Abrir posição</button>
</form>