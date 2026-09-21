<?php
if (!function_exists('App\Core\csrf_field')) {
    require __DIR__ . '/../../Core/Csrf.php';
}

$errors   = $errors   ?? [];
$old      = $old      ?? [];
$contract = $contract ?? null;
?>

<div class="page-header">
    <h1><?= $contract ? 'Editar contrato' : 'Novo contrato' ?></h1>
    <small><a href="/contratos" class="muted">← Voltar</a></small>
</div>

<form class="form form-card" method="post" action="<?= $contract ? '/contratos/' . $contract['id'] : '/contratos' ?>" novalidate>
    <?= \App\Core\csrf_field() ?>

    <div class="form-row">
        <div class="form-group">
            <label for="ativo">Ativo *</label>
            <input type="text" id="ativo" name="ativo" maxlength="20" value="<?= e($old['ativo'] ?? $contract['ativo'] ?? '') ?>">
            <?php if (isset($errors['ativo'])): ?>
                <span class="field-error"><?= e(is_array($errors['ativo']) ? $errors['ativo'][0] : $errors['ativo']) ?></span>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label for="tipo_opcao">Tipo *</label>
            <select id="tipo_opcao" name="tipo_opcao">
                <option value="">Selecione...</option>
                <option value="CALL" <?= (($old['tipo_opcao'] ?? $contract['tipo_opcao'] ?? '') === 'CALL') ? 'selected' : '' ?>>CALL</option>
                <option value="PUT" <?= (($old['tipo_opcao'] ?? $contract['tipo_opcao'] ?? '') === 'PUT') ? 'selected' : '' ?>>PUT</option>
            </select>
            <?php if (isset($errors['tipo_opcao'])): ?>
                <span class="field-error"><?= e(is_array($errors['tipo_opcao']) ? $errors['tipo_opcao'][0] : $errors['tipo_opcao']) ?></span>
            <?php endif; ?>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label for="preco_exercicio">Strike *</label>
            <input type="number" step="0.01" id="preco_exercicio" name="preco_exercicio" value="<?= e($old['preco_exercicio'] ?? $contract['preco_exercicio'] ?? '') ?>">
            <?php if (isset($errors['preco_exercicio'])): ?>
                <span class="field-error"><?= e(is_array($errors['preco_exercicio']) ? $errors['preco_exercicio'][0] : $errors['preco_exercicio']) ?></span>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label for="data_vencto">Vencimento *</label>
            <input type="date" id="data_vencto" name="data_vencto" value="<?= e($old['data_vencto'] ?? $contract['data_vencto'] ?? '') ?>">
            <?php if (isset($errors['data_vencto'])): ?>
                <span class="field-error"><?= e(is_array($errors['data_vencto']) ? $errors['data_vencto'][0] : $errors['data_vencto']) ?></span>
            <?php endif; ?>
        </div>
    </div>
    <div class="form-group">
        <label for="preco_atual">Preço atual *</label>
        <input type="number" step="0.0001" id="preco_atual" name="preco_atual" value="<?= e($old['preco_atual'] ?? $contract['preco_atual'] ?? '') ?>">
        <?php if (isset($errors['preco_atual'])): ?>
            <span class="field-error"><?= e(is_array($errors['preco_atual']) ? $errors['preco_atual'][0] : $errors['preco_atual']) ?></span>
        <?php endif; ?>
    </div>
    <button type="submit" class="btn btn-primary"><?= $contract ? 'Salvar alterações' : 'Cadastrar' ?></button>
</form>