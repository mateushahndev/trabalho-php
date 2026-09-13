<div class="page-header">
    <h1>Novo contrato</h1>
    <small><a href="/contratos" class="muted">← Voltar</a></small>
</div>
<form class="form form-card" method="post" action="/contratos" novalidate>
    <div class="form-row">
        <div class="form-group">
            <label for="ativo">Ativo *</label>
            <input type="text" id="ativo" name="ativo" maxlength="20">
        </div>
        <div class="form-group">
            <label for="tipo_opcao">Tipo *</label>
            <select id="tipo_opcao" name="tipo_opcao">
                <option value="">Selecione...</option>
                <option value="CALL">CALL</option>
                <option value="PUT">PUT</option>
            </select>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label for="preco_exercicio">Strike *</label>
            <input type="number" step="0.01" id="preco_exercicio" name="preco_exercicio">
        </div>
        <div class="form-group">
            <label for="data_vencto">Vencimento *</label>
            <input type="date" id="data_vencto" name="data_vencto">
        </div>
    </div>
    <div class="form-group">
        <label for="preco_atual">Preço atual *</label>
        <input type="number" step="0.0001" id="preco_atual" name="preco_atual">
    </div>
    <button type="submit" class="btn btn-primary">Cadastrar</button>
</form>