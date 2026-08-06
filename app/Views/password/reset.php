<?php
/**
 * Arquivo: Views/password/reset.php
 * Função: VIEW do formulário de redefinição de senha.
 * 
 * Esta tela é exibida após o usuário clicar no link de recuperação
 * enviado por e-mail. O token e o tipo de usuário são passados via GET
 * e armazenados em campos ocultos do formulário.
 * 
 * Ao enviar o formulário, o controller PasswordResetController@resetPassword
 * valida o token e atualiza a senha no banco de dados.
 */

// Título da página (aparecerá na aba do navegador)
$titulo = 'Redefinir Senha';

// Inclui o cabeçalho comum (HTML, CSS, favicon)
require __DIR__ . '/../partials/header.php';
?>

<h1>Nova senha</h1>

<!-- FORMULÁRIO DE REDEFINIÇÃO DE SENHA -->
<!-- Envia os dados via POST para a rota /reset-password -->
<form method="POST" action="/reset-password">
    <!-- Token recebido pelo link (validação de segurança) -->
    <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">
    <!-- Tipo de usuário (admin ou representante) -->
    <input type="hidden" name="tipo" value="<?= htmlspecialchars($_GET['tipo']) ?>">
    
    <label>Nova senha:
        <input type="password" name="senha" required>
    </label>
    
    <label>Confirmar senha:
        <input type="password" name="confirmar_senha" required>
    </label>
    
    <button type="submit">Redefinir senha</button>
</form>

</body>
</html>