<?php
/**
 * Arquivo: Views/password/forgot.php
 * Função: VIEW do formulário "Esqueci minha senha".
 * 
 * Permite que o usuário (administrador ou representante) solicite
 * um link de recuperação de senha informando seu e-mail.
 * O envio é feito via AJAX e a resposta aparece na própria tela.
 */

$titulo = 'Recuperar Senha';
require __DIR__ . '/../partials/header.php';
?>

<div class="login-wrapper">
    <img src="/img/logo-sem-fundo.png" alt="Logo Umbrella" class="logo">
    <div class="login-card">
        <h1>Recuperar senha</h1>
        <p class="subtitle">Informe seu e-mail cadastrado</p>

        <form id="form-forgot" method="POST" action="/forgot-password">
            <div class="input-group">
                <label for="email">
                    <input type="email" id="email" name="email" placeholder="Digite seu e-mail" required>
                </label>
            </div>
            <div class="input-group">
                <label for="tipo">Tipo de usuário:</label>
                <select id="tipo" name="tipo" class="form-select">
                    <option value="representante">Representante</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>
            <div id="forgot-msg" class="login-msg"></div>
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

<script>
document.getElementById('form-forgot').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const msgDiv = document.getElementById('forgot-msg');
    msgDiv.style.display = 'none';

    fetch('/forgot-password', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'   // necessário para o controller identificar AJAX
        }
    })
    .then(response => response.json())
    .then(data => {
        msgDiv.style.display = 'block';
        if (data.sucesso) {
            msgDiv.style.color = '#16a34a';   // verde
            msgDiv.textContent = data.mensagem;
        } else {
            msgDiv.style.color = '#dc2626';   // vermelho
            msgDiv.textContent = data.erro || data.mensagem;
        }
    })
    .catch(() => {
        msgDiv.style.display = 'block';
        msgDiv.style.color = '#dc2626';
        msgDiv.textContent = 'Erro inesperado. Tente novamente.';
    });
});

// Enter em qualquer campo já submete
document.querySelectorAll('#form-forgot input, #form-forgot select').forEach(el => {
    el.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('form-forgot').dispatchEvent(new Event('submit'));
        }
    });
});
</script>

</body>
</html>