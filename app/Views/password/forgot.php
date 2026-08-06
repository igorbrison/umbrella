<?php
/**
 * Arquivo: Views/password/forgot.php
 * Função: VIEW do formulário "Esqueci minha senha".
 * 
 * Permite que o usuário (administrador ou representante) solicite
 * um link de recuperação de senha informando seu e-mail.
 * 
 * O tipo de usuário é selecionado para que o sistema saiba em qual
 * tabela (administradores ou representantes) buscar o e-mail.
 */

// Título da página (aparecerá na aba do navegador)
$titulo = 'Recuperar Senha';

// Inclui o cabeçalho comum (HTML, CSS, favicon)
require __DIR__ . '/../partials/header.php';
?>

<div class="login-wrapper">
    <img src="/img/logo-sem-fundo.png" alt="Logo Umbrella" class="logo">
    <div class="login-card">
        <h1>Recuperar senha</h1>
        <p class="subtitle">Informe seu e-mail para receber o link de redefinição</p>

        <form method="POST" action="/forgot-password">
            <div class="input-group">
                <label for="email">
                    <input type="email" id="email" name="email" placeholder="Digite seu e-mail" required>
                </label>
            </div>

            <div class="input-group">
                <label for="tipo">Tipo de usuário</label>
                <select name="tipo" id="tipo" class="form-select" required>
                    <option value="representante">Representante</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>

            <button type="submit" class="btn-entrar">Enviar link de recuperação</button>
        </form>

        <div class="links-uteis">
            <a href="/login" class="esqueci-senha">Voltar para o login</a>
        </div>
    </div>

    <div class="footer">
        <p>© 2026 UMBRELLA - Todos os direitos reservados</p>
    </div>
</div>

<?php
// Não inclui footer.php porque o header.php já abre e fecha as tags HTML
// O fechamento é feito aqui mesmo
?>
</body>
</html>