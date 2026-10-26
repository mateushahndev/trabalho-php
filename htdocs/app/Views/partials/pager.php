<?php
$pagination = $pagination ?? null;
if ($pagination === null || (int) $pagination['pages'] <= 1 || !is_callable($pagerHref ?? null)) {
    return;
}
$labels = $labels ?? ['item', 'itens'];
?>
<nav class="pager" aria-label="Paginação">
	<?php if ((int) $pagination['page'] > 1): ?>
		<a class="btn btn-ghost btn-sm" href="<?= e($pagerHref((int) $pagination['page'] - 1)) ?>">&laquo; Anterior</a>
	<?php endif; ?>

	<?php for ($i = 1; $i <= (int) $pagination['pages']; $i++): ?>
		<?php if ($i === (int) $pagination['page']): ?>
			<span class="btn btn-primary btn-sm"><?= $i ?></span>
		<?php else: ?>
			<a class="btn btn-ghost btn-sm" href="<?= e($pagerHref($i)) ?>"><?= $i ?></a>
		<?php endif; ?>
	<?php endfor; ?>

	<?php if ((int) $pagination['page'] < (int) $pagination['pages']): ?>
		<a class="btn btn-ghost btn-sm" href="<?= e($pagerHref((int) $pagination['page'] + 1)) ?>">Próxima &raquo;</a>
	<?php endif; ?>

	<span class="pager-info muted">
		Página <?= (int) $pagination['page'] ?> de <?= (int) $pagination['pages'] ?>
		— <?= (int) $pagination['total'] ?> <?= $pagination['total'] === 1 ? e($labels[0]) : e($labels[1]) ?>
	</span>
</nav>
