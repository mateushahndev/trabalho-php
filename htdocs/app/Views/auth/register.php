<?php
 if (!function_exists('App\Core\csrf_field')) {
     require __DIR__ . '/../../Core/Csrf.php';
 }

$errors = $errors ?? [];
$old    = $old    ?? [];
?>

<h1 class="auth-title">Criar conta</h1>
<p class="auth-subtitle">Registre-se para gerenciar suas posições na B3.</p>

<form class="form" method="post" action="/registro" novalidate>
    <?= \App\Core\csrf_field() ?>

    <div class="form-group">
        <label for="nome">Nome completo</label>
        <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($old['nome'] ?? '', ENT_QUOTES) ?>" maxlength="120">
        <?php if (isset($errors['nome'])): ?><span class="field-error"><?= e($errors['nome']) ?></span><?php endif; ?>
    </div>
    <div class="form-group">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES) ?>" maxlength="160">
        <?php if (isset($errors['email'])): ?><span class="field-error"><?= e($errors['email']) ?></span><?php endif; ?>
    </div>
    <div class="form-group">
        <label for="senha">Senha <small>(mín. 6 caracteres)</small></label>
        <input type="password" id="senha" name="senha">
        <?php if (isset($errors['senha'])): ?><span class="field-error"><?= e($errors['senha']) ?></span><?php endif; ?>
    </div>
    <div class="form-group">
        <label for="senha_confirmacao">Confirmar senha</label>
        <input type="password" id="senha_confirmacao" name="senha_confirmacao">
        <?php if (isset($errors['senha_confirmacao'])): ?><span class="field-error"><?= e($errors['senha_confirmacao']) ?></span><?php endif; ?>
    </div>
    <button type="submit" class="btn btn-primary block">Criar conta</button>
</form>

<p class="auth-alt">Já tem conta? <a href="/login">Faça login</a>.</p>