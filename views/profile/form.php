<div class="page-header">
    <h1>Perfil</h1>
</div>
<form class="form form-card" method="post" action="/perfil" novalidate>
    <div class="form-group">
        <label for="nome">Nome completo *</label>
        <input type="text" id="nome" name="nome" maxlength="120">
    </div>
    <div class="form-group">
        <label for="email">E-mail *</label>
        <input type="email" id="email" name="email" maxlength="160">
    </div>
    <hr style="margin:2rem 0">
    <div class="form-group">
        <label for="senha">Nova senha <small>(deixe em branco pra não alterar)</small></label>
        <input type="password" id="senha" name="senha">
    </div>
    <div class="form-group">
        <label for="senha_confirmacao">Confirmar nova senha</label>
        <input type="password" id="senha_confirmacao" name="senha_confirmacao">
    </div>
    <button type="submit" class="btn btn-primary">Salvar</button>
</form>