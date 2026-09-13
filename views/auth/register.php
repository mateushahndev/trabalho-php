<h1 class="auth-title">Criar conta</h1>
<p class="auth-subtitle">Registre-se para gerenciar suas posições na B3.</p>

<form class="form" method="post" action="/registro" novalidate>
    <div class="form-group">
        <label for="nome">Nome completo</label>
        <input type="text" id="nome" name="nome" maxlength="120">
    </div>
    <div class="form-group">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" maxlength="160">
    </div>
    <div class="form-group">
        <label for="senha">Senha <small>(mín. 6 caracteres)</small></label>
        <input type="password" id="senha" name="senha">
    </div>
    <div class="form-group">
        <label for="senha_confirmacao">Confirmar senha</label>
        <input type="password" id="senha_confirmacao" name="senha_confirmacao">
    </div>
    <button type="submit" class="btn btn-primary block">Criar conta</button>
</form>

<p class="auth-alt">Já tem conta? <a href="/login">Faça login</a>.</p>