<?php
if (!function_exists('App\Core\csrf_field')) {
    require __DIR__ . '/../../Core/Csrf.php';
}

$editing  = $contract !== null;
$errors   = $errors ?? [];
$old      = $old    ?? [];

$f = static function (string $k) use ($contract, $old): string {
    if ($contract !== null) return (string) $contract[$k];
    return (string) ($old[$k] ?? '');
};
?>

<div class="page-header">
    <h1><?= $editing ? 'Editar contrato' : 'Nova contrato de opção' ?></h1>
    <small><a href="/contratos" class="muted">← Voltar</a></small>
</div>

<form class="form form-card" method="post" action="<?= $editing ? '/contratos/' . (int) $contract['id'] : '/contratos' ?>" novalidate>
    <?= \App\Core\csrf_field() ?>

    <?php if (isset($errors['_global'])): ?><div class="flash flash-error"><?= e($errors['_global']) ?></div><?php endif; ?>

    <div class="form-row">
        <div class="form-group">
            <label for="ativo">Ativo subjacente *</label>
            <input type="text" id="ativo" name="ativo"<?= $f('ativo') !== '' ? ' value="' . e($f('ativo')) . '"' : '' ?>
                placeholder="Ex: PETR4, ITUB4, IVVB11, BOVA11" required>
            <?php if (isset($errors['ativo'])): ?><span class="field-error"><?= e($errors['ativo']) ?></span><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="tipo_opcao">Tipo de opção *</label>
            <select id="tipo_opcao" name="tipo_opcao" required>
                <option value="">Selecione…</option>
                <option value="CALL"<?= $f('tipo_opcao') === 'CALL' ? ' selected' : '' ?>>CALL (direito de compra)</option>
                <option value="PUT"<?= $f('tipo_opcao') === 'PUT' ? ' selected' : '' ?>>PUT (direito de venda)</option>
            </select>
            <?php if (isset($errors['tipo_opcao'])): ?><span class="field-error"><?= e($errors['tipo_opcao']) ?></span><?php endif; ?>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="preco_exercicio">Preço exercício (strike) *</label>
            <input type="number" id="preco_exercicio" name="preco_exercicio"<?= $f('preco_exercicio') !== '' ? ' value="' . e($f('preco_exercicio')) . '"' : '' ?> required>
            <?php if (isset($errors['preco_exercicio'])): ?><span class="field-error"><?= e($errors['preco_exercicio']) ?></span><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="data_vencto">Data de vencimento *</label>
            <input type="date" id="data_vencto" name="data_vencto"<?= $f('data_vencto') !== '' ? ' value="' . e($f('data_vencto')) . '"' : '' ?> required />
            <?php if (isset($errors['data_vencto'])): ?><span class="field-error"><?= e($errors['data_vencto']) ?></span><?php endif; ?>
        </div>
    </div>

    <div class="form-group">
        <label for="preco_atual">Preço atual de mercado *</label>
        <input type="number" id="preco_atual" name="preco_atual"<?= $f('preco_atual') !== '' ? ' value="' . e($f('preco_atual')) . '"' : '' ?> required>
        <?php if (isset($errors['preco_atual'])): ?><span class="field-error"><?= e($errors['preco_atual']) ?></span><?php endif; ?>
    </div>

    <div>
        <button type="submit" class="btn btn-primary"><?= $editing ? 'Salvar alterações' : 'Cadastrar contrato' ?></button>
    </div>
</form>
