<?php
$editing  = $contract !== null;
$errors   = $errors ?? [];
$old      = $old    ?? [];
$spotData = $spotData ?? [];
$rfr      = $rfr ?? config('finance.risk_free_fallback', 0.14);
$analysis = $analysis ?? null;
$online   = $online ?? false;

$f = static function (string $k) use ($contract, $old): string {
    if ($contract !== null) {
        return (string) $contract[$k];
    }
    return (string) ($old[$k] ?? '');
};

$currentSpot = 0.0;
if ($online && $contract !== null) {
    $a = (string) $contract['ativo'];
    if (isset($spotData[$a])) {
        $currentSpot = (float)($spotData[$a]['spot'] ?? '');
    }
}
?>

<div class="page-header">
    <h1><?= $editing ? 'Editar contrato' : 'Novo contrato de opção' ?></h1>
    <small><a href="/contratos" class="muted">&larr; Voltar à lista</a></small>
</div>

<form class="form form-card" method="post"
    action="<?= $editing ? '/contratos/' . (int) $contract['id'] : '/contratos' ?>" novalidate>
    <?= \App\Core\csrf_field() ?>

    <?php if (isset($errors['_global'])): ?><div class="flash flash-error"><?= e($errors['_global']) ?></div><?php endif; ?>

    <div class="form-row">
        <div class="form-group">
            <label for="ativo">Ativo subjacente *</label>
            <input type="text" id="ativo" name="ativo"<?= $f('ativo') !== '' ? ' value="' . e($f('ativo')) . '"' : '' ?>
                placeholder="Ex: PETR4, ITUB4, IVVB11" maxlength="20" required />
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
            <input type="number" id="preco_exercicio" name="preco_exercicio" step="0.01" min="0.01"<?= $f('preco_exercicio') !== '' ? ' value="' . e($f('preco_exercicio')) . '"' : '' ?> required />
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
        <input type="number" id="preco_atual" name="preco_atual" step="0.0001" min="0"<?= $f('preco_atual') !== '' ? ' value="' . e($f('preco_atual')) . '"' : '' ?> required />
        <?php if (isset($errors['preco_atual'])): ?><span class="field-error"><?= e($errors['preco_atual']) ?></span><?php endif; ?>
    </div>

    <div class="form-group">
        <label for="spot">Spot do ativo <small class="muted">(opcional — para usar preço de mercado, deixar vazio)</small></label>
        <input type="number" id="spot" name="spot" step="0.0001" min="0" value="<?= e(round($currentSpot, 2)) ?>"
            placeholder="Deixe vazio para usar preço do Yahoo (requer Conexão)" />
    </div>

    <div class="form-grid-row">
        <button type="submit" class="btn btn-primary"><?= $editing ? 'Salvar' : 'Cadastrar' ?></button>
        <?php if ($editing): ?><a class="btn btn-ghost" href="/contratos">Cancelar</a><?php endif; ?>
        <button type="button" class="btn btn-ghost btn-sm btn-bs-preview" id="btn-bs-preview">📈 Análise BS</button>
    </div>

    <div id="bs-preview" class="bs-panel">
        <?php if ($analysis !== null): ?>
            <div class="bs-table">
                <div class="bs-row bs-head">
                    <span class="bs-label">Preço Teórico (BS)</span>
                    <span class="bs-value">R$ <?= e(round($analysis['theoretical_price'], 2)) ?></span>
                </div>
                <div class="bs-row">
                    <span class="bs-label">Preço de Mercado</span>
                    <span class="bs-value">R$ <?= e(round($f('preco_atual'), 2)) ?></span>
                </div>
                <?php $diff = round($analysis['theoretical_price'] - $f('preco_atual'), 2); ?>
                <div class="bs-row">
                    <span class="bs-label">Teórica vs Mercado</span>
                    <span class="bs-value"><?= $diff >= 0 ? '+' : '' ?>R$ <?= e($diff) ?></span>
                </div>
                <?php $ivDisplay = $analysis['volatility'] !== null
                    ? round($analysis['volatility'] * 100, 2) . '%' : '—'; ?>
                <div class="bs-row">
                    <span class="bs-label">Vol. Implícita</span>
                    <span class="bs-value"><?= e($ivDisplay) ?></span>
                </div>
            </div>
            <div class="bs-greeks">
                <?php foreach ($analysis['greeks'] as $key => $val): ?>
                    <div class="bs-cell">
                        <span class="bs-label"><?= e(strtoupper($key[0])) ?></span>
                        <span class="bs-value"><?= e(round($val, 2)) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="empty">Clique em <strong>Análise BS</strong> para ver o preço teórico, IV e gregos.</p>
        <?php endif; ?>
    </div>
</form>
