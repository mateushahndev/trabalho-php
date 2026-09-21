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
    <h1>Nova posição</h1>
    <small><a href="/posicoes" class="muted">← Voltar</a></small>
</div>
<form class="form form-card" method="post" action="/posicoes" novalidate>
    <?= App\Core\csrf_field() ?>

    <?php if (isset($errors['_global'])): ?><div class="flash flash-error"><?= e($errors['_global']) ?></div><?php endif; ?>

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
            <label for="quantidade">Quantidade *</label>
            <input type="number" id="quantidade" name="quantidade"<?= $f('quantidade') !== '' ? ' value="' . $f('quantidade') . '"' : '' ?> required>
            <?php if (isset($errors['quantidade'])): ?><span class="field-error"><?= e($errors['quantidade']) ?></span><?php endif; ?>
        </div>
        <div class="form-group">
            <label for="preco_medio">Preço médio *</label>
            <input type="number" step="0.0001" id="preco_medio" name="preco_medio"<?= $f('preco_medio') !== '' ? ' value="' . e($f('preco_medio')) . '"' : '' ?> required>
            <?php if (isset($errors['preco_medio'])): ?><span class="field-error"><?= e($errors['preco_medio']) ?></span><?php endif; ?>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Abrir posição</button>
</form>
