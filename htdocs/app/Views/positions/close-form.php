<?php
// Carrega Csrf helpers (define csrf_field())
if (!function_exists('App\Core\csrf_field')) {
    require __DIR__ . '/../../Core/Csrf.php';
}

$position = $position ?? [];
$errors   = $errors   ?? [];
$old      = $old      ?? [];

$f   = static function (string $k) use ($old): string { return htmlspecialchars($old[$k] ?? '', ENT_QUOTES); };
?>

<div class="page-header">
    <h1>Fechar posição</h1>
    <small><a href="/posicoes" class="muted">← Voltar</a></small>
</div>
<div class="info-box">
    <strong>Resumo:</strong>
    <ul style="margin:.5rem 0 0;padding-left:1.25rem">
        <li>Contrato: <strong><?= e($position['ativo']) ?></strong> <?= e($position['tipo_opcao']) ?> R$ <?= e(money((float)$position['preco_exercicio'])) ?></li>
        <li>Quantidade aberta: <strong><?= (int) $position['quantidade_aberta'] ?></strong></li>
        <li>Preço médio: <strong>R$ <?= e(money((float)$position['preco_medio'])) ?></strong></li>
    </ul>
</div>
<form class="form form-card" method="post" action="/posicoes/<?= (int) $position['id'] ?>/fechar" novalidate>
    <?= App\Core\csrf_field() ?>

    <div style="display:none"><input type="hidden" name="contrato_id" value="<?= (int) ($position['contrato_id'] ?? 0) ?>" /></div>

    <?php if (isset($errors['_global'])): ?><div class="flash flash-error"><?= e($errors['_global']) ?></div><?php endif; ?>
    <div class="form-group">
        <label for="quantidade">Quantidade a fechar *</label>
        <input type="number" id="quantidade" name="quantidade" value="<?= htmlspecialchars($f('quantidade'), ENT_QUOTES) ?>" required>
        <small class="muted">Máximo: <?= (int) $position['quantidade_aberta'] ?></small>
        <?php if (isset($errors['quantidade'])): ?><span class="field-error"><?= e($errors['quantidade']) ?></span><?php endif; ?>
    </div>
    <div class="form-group">
        <label for="preco">Preço de venda *</label>
        <input type="number" step="0.01" id="preco" name="preco"<?= $f('preco') !== '' ? ' value="' . e($f('preco')) . '"' : '' ?> required>
        <?php if (isset($errors['preco'])): ?><span class="field-error"><?= e($errors['preco']) ?></span><?php endif; ?>
    </div>
    <button type="submit" class="btn btn-primary">Fechar posição</button>
</form>
