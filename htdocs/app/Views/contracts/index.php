<?php
$contracts = $contracts ?? [];
?>

<div class="page-header">
	<h1>Contratos</h1>
	<a class="btn btn-primary" href="/contratos/novo">+ Novo contrato</a>
</div>

<section class="panel">
	<?php if ($contracts === []): ?>
		<p class="empty">Nenhum contrato cadastrado. <a href="/contratos/novo">Cadastre o primeiro</a>.</p>
	<?php else: ?>
		<div class="table-wrap">
			<table>
				<thead>
					<tr>
						<th>Ativo subjacente</th>
						<th>Tipo de opção</th>
						<th class="num">Strike</th>
						<th>Vencimento</th>
						<th class="num">Preço atual (mercado)</th>
						<th>Ações</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($contracts as $c): ?>
						<tr>
							<td><strong><?= e($c['ativo']) ?></strong></td>
							<td><span class="badge badge-<?= strtolower((string) $c['tipo_opcao']) ?>"><?= e($c['tipo_opcao']) ?></span></td>
							<td class="num">R$ <?= e(money((float) $c['preco_exercicio'])) ?></td>
							<td><?= e(br_date($c['data_vencto'])) ?></td>
							<td class="num">R$ <?= e(money((float) $c['preco_atual'], 4)) ?></td>
							<td><a class="btn btn-ghost btn-sm" href="/contratos/<?= (int) $c['id'] ?>/editar">Editar</a></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
</section>

<?php $labels = ['contrato', 'contratos']; include __DIR__ . '/../partials/pager.php'; ?>
