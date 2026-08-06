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

<h1>Recuperar senha</h1>

<!-- FORMULÁRIO DE RECUPERAÇÃO -->
<!-- Envia os dados via POST para a rota /forgot-password -->
<form method="POST" action="/forgot-password">
    <label>Email:
        <input type="email" name="email" required>
    </label>
    
    <label>Tipo de usuário:
        <select name="tipo">
            <option value="representante">Representante</option>
            <option value="admin">Administrador</option>
        </select>
    </label>

    <button type="submit">Enviar link de recuperação</button>
</form>

</body>
</html>