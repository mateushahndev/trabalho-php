<?php
// Carrega Csrf helpers (define csrf_field()) se ainda não existir
if (!function_exists('App\Core\csrf_field')) {
    require __DIR__ . '/../../Core/Csrf.php';
}

/** @var array $contracts */
/** @var array $errors */
/** @var array $old */
/** @var array|null $result */

$contracts = $contracts ?? [];
$errors    = $errors ?? [];
$old       = $old ?? [];
$result    = $result ?? null;

$f = static function (string $k) use ($old): string { return htmlspecialchars($old[$k] ?? '', ENT_QUOTES); };
$sel = static function (string $k, string $v) use ($old): string { return (($old[$k] ?? '') === $v ? ' selected' : ''); };
?>

<div class="page-header">
	<h1>Calculadora de Opções</h1>
	<small class="muted">Calcule o valor intrínseco e o status da sua opção.</small>
</div>

<div class="form-row" style="align-items: flex-start;">
	<!-- Formulário -->
	<div class="form form-card" style="flex: 1;">
		<form method="post" action="/analise" novalidate>
			<?= App\Core\csrf_field() ?>

			<div class="form-group">
				<label for="contrato_id">Contrato *</label>
				<select id="contrato_id" name="contrato_id" required>
					<option value="">Selecione um contrato…</option>
					<?php foreach ($contracts as $c): ?>
						<option value="<?= (int) $c['id'] ?>"<?= $sel('contrato_id', (string) $c['id']) ?>>
							<?= e(\App\Models\Contract::label($c)) ?> &mdash; <?= e(br_date($c['data_vencto'])) ?>
						</option>
					<?php endforeach; ?>
				</select>
				<?php if (isset($errors['contrato_id'])): ?><span class="field-error"><?= e($errors['contrato_id']) ?></span><?php endif; ?>
			</div>

			<div class="form-group">
				<label for="preco_ativo">Preço Atual do Ativo (Spot) *</label>
				<input type="number" step="0.01" min="0.01" id="preco_ativo" name="preco_ativo" value="<?= $f('preco_ativo') ?>" required>
				<?php if (isset($errors['preco_ativo'])): ?><span class="field-error"><?= e($errors['preco_ativo']) ?></span><?php endif; ?>
			</div>

			<button type="submit" class="btn btn-primary">Calcular</button>
		</form>
	</div>

	<!-- Resultado -->
	<div class="form form-card" style="flex: 1;">
		<h2 class="auth-title" style="font-size: 1.5rem; margin-bottom: 1rem;">Resultado</h2>
		<?php if ($result !== null): ?>
			<div class="info-box">
				<ul style="margin:0; padding-left: 1.25rem;">
					<li>Contrato: <strong><?= e($result['contract']['ativo']) ?> (<?= e($result['tipo']) ?>)</strong></li>
					<li>Strike: <strong>R$ <?= e(money($result['strike'])) ?></strong></li>
					<li>Preço do Ativo: <strong>R$ <?= e(money($result['preco_ativo'])) ?></strong></li>
					<hr style="margin: 0.5rem 0; border: none; border-top: 1px solid var(--border);">
					<li>Valor Intrínseco: <strong style="font-size: 1.2rem;">R$ <?= e(money($result['valor_intrinseco'])) ?></strong></li>
					<li>Status: <strong class="<?= str_contains($result['status'], 'In') ? 'text-up' : (str_contains($result['status'], 'Out') ? 'text-down' : '') ?>"><?= e($result['status']) ?></strong></li>
				</ul>
			</div>
		<?php else: ?>
			<p class="empty">Preencha o formulário ao lado e clique em Calcular para ver o resultado.</p>
		<?php endif; ?>
	</div>
</div>
