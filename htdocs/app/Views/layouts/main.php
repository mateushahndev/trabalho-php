<?php
if (!function_exists('App\Core\csrf_field')) {
  require __DIR__ . '/../../Core/Csrf.php';
}

$activeNav = $activeNav ?? '';
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? config('app.name')) ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<nav class="topbar">
    <div class="container topbar-inner">
        <a class="brand" href="/">Opções<span>B3</span></a>
        <?php if ($user !== null): ?>
        <div class="nav-links">
            <a href="/" class="<?= $activeNav === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
            <a href="/contratos" class="<?= $activeNav === 'contratos' ? 'active' : '' ?>">Contratos</a>
            <a href="/posicoes" class="<?= $activeNav === 'posicoes' ? 'active' : '' ?>">Posições</a>
            <a href="/transacoes" class="<?= $activeNav === 'transacoes' ? 'active' : '' ?>">Transações</a>
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
<main class="container main">
    <?php require __DIR__ . '/../partials/flash.php'; ?>
    <?= $content ?>
</main>
<footer class="footer">
    <div class="container">
        <p>Opções B3 - Projeto acadêmico</p>
    </div>
</footer>
</body>
</html>
