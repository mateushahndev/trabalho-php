<?php
// Carrega Csrf helpers (define csrf_field())
if (!function_exists('App\Core\csrf_field')) {
  require __DIR__ . '/../../Core/Csrf.php';
}

$contracts = $contracts ?? [];
$errors    = $errors    ?? [];
$old       = $old       ?? [];

$f   = static function (string $k) use ($old): string { return htmlspecialchars($old[$k] ?? '', ENT_QUOTES); };
$sel = static function (string $k, string $v) use ($old): string { return (($old[$k] ?? '') === $v ? ' selected' : ''); };
?>

<div class="page-header">
    <h1>Nova transação</h1>
    <small><a href="/transacoes" class="muted">← Voltar</a></small>
</div>
<form class="form form-card" method="post" action="/transacoes" novalidate>
    <?= \App\Core\csrf_field() ?>

    <div class="form-group">
        <label for="contrato_id">Contrato *</label>
        <select id="contrato_id" name="contrato_id">
            <option value="">Selecione...</option>
            <?php foreach ($contracts as $c): ?>
                <option value="<?= (int) $c['id'] ?>"<?= $sel('contrato_id', (string) $c['id']) ?>>
		<?= e(\App\Models\Contract::label($c)) ?> - <?= e(br_date($c['data_vencto'])) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($errors['contrato_id'])): ?><span class="field-error"><?= e($errors['contrato_id']) ?></span><?php endif; ?>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label for="operacao">Operação *</label>
            <select id="operacao" name="operacao">
                <option value="COMPRA"<?= $sel('operacao', 'COMPRA') ?>>COMPRA</option>
                <option value="VENDA"<?= $sel('operacao', 'VENDA') ?>>VENDA</option>
            </select>
            <?php if (isset($errors['operacao'])): ?><span class="field-error"><?= e($errors['operacao']) ?></span><?php endif; ?>
        </div>
        <div class="form-group">
            <label for="quantidade">Quantidade *</label>
            <input type="number" id="quantidade" name="quantidade" <?= $f('quantidade') !== '' ? ' value="' . $f('quantidade') . '"' : '' ?> required>
            <?php if (isset($errors['quantidade'])): ?><span class="field-error"><?= e($errors['quantidade']) ?></span><?php endif; ?>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label for="preco">Preço *</label>
            <input type="number" step="0.01" id="preco" name="preco"<?= $f('preco') !== '' ? ' value="' . e($f('preco')) . '"' : '' ?> required>
            <?php if (isset($errors['preco'])): ?><span class="field-error"><?= e($errors['preco']) ?></span><?php endif; ?>
        </div>
        <div class="form-group">
            <label for="comissao">Comissão</label>
            <input type="number" step="0.01" id="comissao" name="comissao"<?= $f('comissao') !== '' ? ' value="' . e($f('comissao')) . '"' : '' ?>>
            <?php if (isset($errors['comissao'])): ?><span class="field-error"><?= e($errors['comissao']) ?></span><?php endif; ?>
	</div>
    </div>
    <div class="form-group">
        <label for="data_transacao">Data/hora</label>
        <input type="datetime-local" id="data_transacao" name="data_transacao"<?= $f('data_transacao') !== '' ? ' value="' . htmlspecialchars($f('data_transacao'), ENT_QUOTES) . '"' : '' ?>>
    <?php if (isset($errors['data_transacao'])): ?><span class="field-error"><?= e($errors['data_transacao']) ?></span><?php endif; ?>
    </div>
    <button type="submit" class="btn btn-primary">Registrar</button>
</form>
