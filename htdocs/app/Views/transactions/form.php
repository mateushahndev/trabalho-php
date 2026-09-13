<div class="page-header">
    <h1>Nova transação</h1>
    <small><a href="/transacoes" class="muted">← Voltar</a></small>
</div>
<form class="form form-card" method="post" action="/transacoes" novalidate>
    <div class="form-group">
        <label for="contrato_id">Contrato *</label>
        <select id="contrato_id" name="contrato_id">
            <option value="">Selecione...</option>
            <?php foreach ($contracts as $c): ?>
                <option value="<?= $c['id'] ?>">
                    <?= $c['ativo'] ?> <?= $c['tipo_opcao'] ?> R$ <?= number_format($c['preco_exercicio'], 2, ',', '.') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label for="operacao">Operação *</label>
            <select id="operacao" name="operacao">
                <option value="COMPRA">COMPRA</option>
                <option value="VENDA">VENDA</option>
            </select>
        </div>
        <div class="form-group">
            <label for="quantidade">Quantidade *</label>
            <input type="number" id="quantidade" name="quantidade">
        </div>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label for="preco">Preço *</label>
            <input type="number" step="0.0001" id="preco" name="preco">
        </div>
        <div class="form-group">
            <label for="comissao">Comissão</label>
            <input type="number" step="0.01" id="comissao" name="comissao">
        </div>
    </div>
    <div class="form-group">
        <label for="data_transacao">Data/hora</label>
        <input type="datetime-local" id="data_transacao" name="data_transacao">
    </div>
    <button type="submit" class="btn btn-primary">Registrar</button>
</form>