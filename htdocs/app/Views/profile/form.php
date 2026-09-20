<?php
// Carrega Csrf helpers (define csrf_field())
require __DIR__ . '/../../Core/Csrf.php';

$user     = $user     ?? null;
$errors   = $errors   ?? [];
$old      = $old      ?? [];

$f   = static function (string $k) use ($old): string { return htmlspecialchars($old[$k] ?? '', ENT_QUOTES); };

if ($user === null) {
	echo '<p class="flash flash-error">Sessão expirada.</p>';
	return;
}
?>

<div class="page-header">
    <h1>Perfil</h1>
</div>
<form class="form form-card" method="post" action="/perfil" novalidate>
    <?= \App\Core\csrf_field() ?>

    <?php if (isset($errors['_global'])): ?><div class="flash flash-error"><?= e($errors['_global']) ?></div><?php endif; ?>

    <div class="form-group">
        <label for="nome">Nome completo *</label>
        <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($old['nome'] ?? '') ?>" maxlength="120" required>
        <?php if (isset($errors['nome'])): ?><span class="field-error"><?= e($errors['nome']) ?></span><?php endif; ?>
    </div>
    <div class="form-group">
        <label for="email">E-mail *</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" maxlength="160" required>
        <?php if (isset($errors['email'])): ?><span class="field-error"><?= e($errors['email']) ?></span><?php endif; ?>
    </div>
    <hr style="margin:2rem 0">
    <div class="form-group">
        <label for="senha">Nova senha <small>(deixe em branco pra não alterar)</small></label>
        <input type="password" id="senha" name="senha">
        <?php if (isset($errors['senha'])): ?><span class="field-error"><?= e($errors['senha']) ?></span><?php endif; ?>
    </div>
    <div class="form-group">
        <label for="senha_confirmacao">Confirmar nova senha</label>
        <input type="password" id="senha_confirmacao" name="senha_confirmacao">
        <?php if (isset($errors['senha_confirmacao'])): ?><span class="field-error"><?= e($errors['senha_confirmacao']) ?></span><?php endif; ?>
    </div>
    <button type="submit" class="btn btn-primary">Salvar</button>
</form>

<p style="margin-top:2rem">Conta criada em <?= e(br_date($user['criado_em'])) ?>.</p>
