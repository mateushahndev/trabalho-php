<?php
// Carrega Csrf helpers (define csrf_field())
if (!function_exists('App\Core\csrf_field')) {
    require __DIR__ . '/../../Core/Csrf.php';
}

$errors = $errors ?? [];
$old    = $old    ?? [];
$username = htmlspecialchars($old['email'] ?? '', ENT_QUOTES);
?>

<h1 class="auth-title">Entrar</h1>
<p class="auth-subtitle">Acesse suas posições e o histórico de negociações na B3.</p>

<form class="form" method="post" action="/login" novalidate>
    <?= \App\Core\csrf_field() ?>

    <div class="form-group">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" <?= $username !== '' ? 'value="' . $username . '"' : '' ?> required>
        <?php if (isset($errors['email'])): ?><span class="field-error"><?= e($errors['email']) ?></span><?php endif; ?>
    </div>
    <div class="form-group">
        <label for="senha">Senha</label>
        <input type="password" id="senha" name="senha" required>
        <?php if (isset($errors['senha'])): ?><span class="field-error"><?= e($errors['senha']) ?></span><?php endif; ?>
    </div>
    <button type="submit" class="btn btn-primary block">Entrar</button>
</form>

<p class="auth-alt">Não tem conta? <a href="/registro">Registre-se agora</a>.</p>
