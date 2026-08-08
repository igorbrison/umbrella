<?php
/**
 * Arquivo: Views/password/reset.php
 * Função: VIEW do formulário de redefinição de senha.
 * 
 * Esta tela é exibida após o usuário clicar no link de recuperação
 * enviado por e-mail.
 */

$titulo = 'Redefinir Senha';
require __DIR__ . '/../partials/header.php';
?>

<div class="login-wrapper">
    <img src="/img/logo-sem-fundo.png" alt="Logo Umbrella" class="logo">
    <div class="login-card">
        <h1>Nova senha</h1>
        <p class="subtitle">Digite sua nova senha abaixo</p>

        <form method="POST" action="/reset-password">
            <!-- Token e tipo recebidos pelo link -->
            <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">
            <input type="hidden" name="tipo" value="<?= htmlspecialchars($_GET['tipo']) ?>">

            <div class="input-group">
                <label for="senha">
                    <input type="password" id="senha" name="senha" placeholder="Nova senha" required>
                </label>
            </div>
            <div class="input-group">
                <label for="confirmar_senha">
                    <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Confirmar senha" required>
                </label>
            </div>
            <button type="submit" class="btn-entrar">Redefinir senha</button>
        </form>
    </div>

    <div class="footer">
        <p>© 2026 UMBRELLA - Todos os direitos reservados</p>
    </div>
</div>

</body>
</html>