<h1 class="auth-title">Entrar</h1>
<p class="auth-subtitle">Acesse suas posições e o histórico de negociações na B3.</p>

<form class="form" method="post" action="/login" novalidate>
    <div class="form-group">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email">
    </div>
    <div class="form-group">
        <label for="senha">Senha</label>
        <input type="password" id="senha" name="senha">
    </div>
    <button type="submit" class="btn btn-primary block">Entrar</button>
</form>

<p class="auth-alt">Não tem conta? <a href="/registro">Registre-se agora</a>.</p>