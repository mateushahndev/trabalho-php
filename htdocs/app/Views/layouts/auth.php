<!doctype html>
<html lang="pt-BR">

<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title><?= e($pageTitle ?? config('app.name')) ?></title>
	<link rel="stylesheet" href="assets/css/style.css" />
</head>

<body class="auth-body">
	<div class="auth-card">
		<a class="brand auth-brand" href="/">Opções<span>B3</span></a>
		<?php include __DIR__ . '/../partials/flash.php'; ?>
		<?= $content ?>
	</div>
</body>

</html>
