<?php
/**
 * Arquivo: Views/login.php
 * Função: VIEW do formulário de login do representante.
 * 
 * O formulário é enviado via AJAX; mensagens de erro aparecem na tela.
 * Pressionar Enter em qualquer campo já dispara o login.
 */

$titulo = 'Login Representante';
require __DIR__ . '/partials/header.php';
?>

<div class="login-wrapper">
    <img src="/img/logo-sem-fundo.png" alt="Logo Umbrella" class="logo">
    <div class="login-card">
        <h1>Bem-vindo</h1>
        <p class="subtitle">Acesse sua conta de representante</p>

        <form id="form-login" method="POST" action="/login">
            <div class="input-group">
                <label for="cnpj">
                    <input type="text" id="cnpj" name="cnpj" placeholder="Digite seu CNPJ" required>
                </label>
            </div>
            <div class="input-group">
                <label for="senha">
                    <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
                </label>
            </div>
            <div id="login-msg" class="login-msg"></div>
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
document.getElementById('form-login').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('/login', { method: 'POST', body: formData })
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
document.querySelectorAll('#form-login input').forEach(input => {
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('form-login').dispatchEvent(new Event('submit'));
        }
    });
});
</script>

</body>
</html>