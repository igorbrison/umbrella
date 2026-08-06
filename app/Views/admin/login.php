<?php
/**
 * Arquivo: Views/admin/login.php
 * Função: VIEW do formulário de login administrativo.
 * 
 * Apenas o administrador (dono da empresa) tem acesso a esta área.
 * Após a autenticação, o admin gerencia representantes, módulos,
 * licenças, clientes e configurações do sistema.
 */

// Título da página (usado no <title>)
$titulo = 'Login Administrativo';

// Inclui o cabeçalho comum (HTML, CSS, favicon)
require __DIR__ . '/../partials/header.php';
?>

<!-- CORPO DA PÁGINA (estrutura idêntica à tela de representantes) -->
<div class="login-wrapper">
    <img src="/img/logo-sem-fundo.png" alt="Logo Umbrella" class="logo">
    <div class="login-card">
        <h1>Bem-vindo</h1>
        <p class="subtitle">Acesse sua conta administrativa</p>

        <form method="POST" action="/admin/login">
            <div class="input-group">
                <label for="email">
                    <input type="email" id="email" name="email" placeholder="Digite seu nome de usuário" required>
                </label>
            </div>
            <div class="input-group">
                <label for="senha">
                    <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
                </label>
            </div>
            <button type="submit" class="btn-entrar">Entrar</button>
        </form>

        <div class="links-uteis">
            <a href="/forgot-password" class="esqueci-senha">Esqueci minha senha</a>
        </div>
    </div>

    <!-- Rodapé -->
    <div class="footer">
        <p>© 2026 UMBRELLA - Todos os direitos reservados</p>
    </div>
</div>

<?php
// Inclui o rodapé comum (se houver)
// require __DIR__ . '/../partials/footer.php';
?>
</body>
</html>