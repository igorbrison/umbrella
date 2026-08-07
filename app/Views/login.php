<?php
/**
 * Arquivo: Views/login.php
 * Função: VIEW do formulário de login do representante.
 * 
 * Exibe a tela de entrada para representantes, contendo:
 *   - Logotipo da empresa.
 *   - Campos de CNPJ (nome de usuário) e senha.
 *   - Botão "Entrar" que envia o formulário para POST /login.
 *   - Link "Esqueci minha senha" para recuperação de credenciais.
 *   - Rodapé com direitos autorais.
 * 
 * Uso: Carregada pelo AuthController@loginForm.
 * Utiliza o partial header.php para o cabeçalho HTML.
 * 
 * A sessão não é verificada nesta tela (rota pública).
 */

// Título da página (aparecerá na aba do navegador)
$titulo = 'Login Representante';

// Inclui o cabeçalho comum (HTML, CSS, favicon)
require __DIR__ . '/partials/header.php';
?>

<div class="login-wrapper">
    <img src="/img/logo-sem-fundo.png" alt="Logo Umbrella" class="logo">
    <div class="login-card">
        <h1>Bem-vindo</h1>
        <p class="subtitle">Acesse sua conta de representante</p>

        <form method="POST" action="/login">
            <div class="input-group">
                <label for="cnpj">
                    <input type="text" id="cnpj" name="cnpj" placeholder="Digite seu nome de usuário" required>
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

    <div class="footer">
        <p>© 2026 UMBRELLA - Todos os direitos reservados</p>
    </div>
</div>

</body>
</html>