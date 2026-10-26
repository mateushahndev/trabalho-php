<?php
/** @var string $content */
/** @var string $pageTitle   (default: config app name) */
/** @var string $activeNav   = 'dashboard'|'contratos'|'posicoes'|'transacoes'|'perfil'|'' */
/** @var array<string, mixed>|null $user */
$activeNav = $activeNav ?? '';
?>
<!doctype html>
<html lang="pt-BR">

<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title><?= e($pageTitle ?? config('app.name')) ?></title>
	<link rel="stylesheet" href="/assets/css/style.css" />
</head>

<body>

	<!-- ======================== TOP BAR ======================== -->
	<nav class="topbar">
		<div class="container topbar-inner">
			<a class="brand" href="/">Opções<span>B3</span></a>

			<?php if ($user !== null): ?>
				<div class="nav-links">
					<a href="/" class="<?= $activeNav === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
					<a href="/contratos" class="<?= $activeNav === 'contratos' ? 'active' : '' ?>">Contratos</a>
					<a href="/posicoes" class="<?= $activeNav === 'posicoes' ? 'active' : '' ?>">Posições</a>
					<a href="/transacoes" class="<?= $activeNav === 'transacoes' ? 'active' : '' ?>">Transações</a>
					<a href="/mercados" class="<?= $activeNav === 'mercados' ? 'active' : '' ?>">Mercados</a>
					<a href="/analise" class="<?= $activeNav === 'analise' ? 'active' : '' ?>">Análise</a>
					<a href="/perfil" class="<?= $activeNav === 'perfil' ? 'active' : '' ?>">Perfil</a>
				</div>
			<?php endif; ?>

			<div class="topbar-actions">
				<?php if ($user !== null): ?>
					<span class="user-chip" title="<?= e($user['email']) ?>"><?= e($user['nome']) ?></span>
					<form class="inline-form" method="post" action="/logout">
						<?= \App\Core\csrf_field() ?>
						<button type="submit" class="btn btn-ghost">Sair</button>
					</form>
				<?php else: ?>
					<a class="btn btn-ghost" href="/login">Entrar</a>
					<a class="btn btn-primary" href="/registro">Registrar</a>
				<?php endif; ?>
			</div>
		</div>
	</nav>

	<!-- ======================== MAIN ======================== -->
	<main class="container main">
		<?php include __DIR__ . '/../partials/flash.php'; ?>
		<?= $content ?>
	</main>

	<footer class="footer">
		<div class="container">
			<p>Opções B3 &mdash; Projeto acadêmico -- negociação de op&ccedil;&otilde;es sobre ações e &iacute;ndices.<br />
				Dados fictícios, n&atilde;o constituem recomendação de investimento.</p>
		</div>
	</footer>

	<script src="/assets/js/app.js"></script>
</body>

</html>
