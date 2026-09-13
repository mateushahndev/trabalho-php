<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Opções B3</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
</head>
<body>
<nav class="topbar">
    <div class="container topbar-inner">
        <a class="brand" href="/">Opções<span>B3</span></a>
        <div class="nav-links">
            <a href="/">Dashboard</a>
            <a href="/contratos">Contratos</a>
            <a href="/posicoes">Posições</a>
            <a href="/transacoes">Transações</a>
            <a href="/perfil">Perfil</a>
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
