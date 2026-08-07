<?php
/**
 * Arquivo: Views/admin/login.php
 * Função: VIEW do formulário de login administrativo.
 * 
 * Apenas o administrador (dono da empresa) tem acesso a esta área.
 * O formulário é enviado via AJAX; mensagens de erro aparecem na tela.
 * Pressionar Enter em qualquer campo já dispara o login.
 */

$titulo = 'Login Administrativo';
require __DIR__ . '/../partials/header.php';
?>

<div class="login-wrapper">
    <img src="/img/logo-sem-fundo.png" alt="Logo Umbrella" class="logo">
    <div class="login-card">
        <h1>Bem-vindo</h1>
        <p class="subtitle">Acesse sua conta administrativa</p>

        <form id="form-login-admin" method="POST" action="/admin/login">
            <div class="input-group">
                <label for="email">
                    <input type="email" id="email" name="email" placeholder="Digite seu email" required>
                </label>
            </div>
            <div class="input-group">
                <label for="senha">
                    <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
                </label>
            </div>
            <div id="login-msg" style="color: red; margin-bottom: 10px; display: none;"></div>
            <button type="submit" class="btn-entrar">Entrar</button>
        </form>

        <div class="links-uteis">
            <a href="/forgot-password" class="esqueci-senha">Esqueci minha senha</a>
        </div>
    </div>

    <div class="footer">
        <p>© 2026 UMBRELLA - Todos os direitos reservados</p>
    </div>
</div>

<script>
document.getElementById('form-login-admin').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('/admin/login', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if (data.sucesso) {
                window.location.href = data.redirect;
            } else {
                document.getElementById('login-msg').textContent = data.erro;
                document.getElementById('login-msg').style.display = 'block';
            }
        });
});

// Garante que Enter em qualquer input submeta o formulário
document.querySelectorAll('#form-login-admin input').forEach(input => {
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('form-login-admin').dispatchEvent(new Event('submit'));
        }
    });
});
</script>

</body>
</html>