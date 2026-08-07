<?php
/**
 * Arquivo: Views/admin/perfil/form.php
 * Função: VIEW do formulário de edição do perfil do administrador.
 * 
 * Permite que o administrador altere seu nome e email.
 * 
 * Variáveis esperadas:
 *   - $admin (array)  : Dados atuais do administrador (nome, email)
 *   - $sucessoSenha    : Mensagem de sucesso da alteração de senha (via sessão)
 *   - $erroSenha       : Mensagem de erro da alteração de senha (via sessão)
 * 
 * Uso dos parciais:
 *   - dashboard_header.php : barra superior, menu lateral e abertura do main-content.
 *   - dashboard_footer.php : fechamento das tags abertas pelo header.
 */

// Inicializa $admin como array vazio se não definido (evita avisos de análise)
if (!isset($admin) || !is_array($admin)) {
    $admin = [];
}

// Título da página
$titulo = 'Editar Perfil - Admin';

// Inclui o cabeçalho do painel (barra superior, menu lateral, abertura do main-content)
require __DIR__ . '/../../partials/dashboard_header.php';

// Mensagens de retorno da alteração de senha (armazenadas na sessão pelo SenhaController)
$sucessoSenha = $_SESSION['sucesso_senha'] ?? null;
$erroSenha    = $_SESSION['erro_senha'] ?? null;
unset($_SESSION['sucesso_senha'], $_SESSION['erro_senha']); // limpa após exibição
?>

<h1>Meu Perfil</h1>

<!-- Mensagem de sucesso (se houver) -->
<?php if ($sucessoSenha): ?>
    <div class="mensagem sucesso"><?= htmlspecialchars($sucessoSenha) ?></div>
<?php endif; ?>

<!-- Mensagem de erro (se houver) -->
<?php if ($erroSenha): ?>
    <div class="mensagem erro"><?= htmlspecialchars($erroSenha) ?></div>
<?php endif; ?>

<!-- FORMULÁRIO DE EDIÇÃO DO PERFIL -->
<form method="POST" action="/admin/perfil/salvar">
    <label>Nome:
        <input type="text" name="nome" value="<?= htmlspecialchars($admin['nome'] ?? '') ?>" required>
    </label>
    
    <label>Email:
        <input type="email" name="email" value="<?= htmlspecialchars($admin['email'] ?? '') ?>" required>
    </label>
    
    <button type="submit">Salvar</button>
</form>

<p><a href="/admin/representantes">Voltar</a></p>

<?php
// Inclui o rodapé do painel (fecha main-content, div wrapper, body e html)
require __DIR__ . '/../../partials/dashboard_footer.php';
?>