<?php
/**
 * Arquivo: Views/admin/perfil/form.php
 * Função: VIEW do formulário de edição do perfil do administrador.
 */

if (!isset($admin) || !is_array($admin)) {
    $admin = [];
}

$titulo = 'Editar Perfil - Admin';
require __DIR__ . '/../../partials/dashboard_header.php';

$sucessoSenha = $_SESSION['sucesso_senha'] ?? null;
$erroSenha    = $_SESSION['erro_senha'] ?? null;
unset($_SESSION['sucesso_senha'], $_SESSION['erro_senha']);
?>

<h1>Meu Perfil</h1>

<?php if ($sucessoSenha): ?>
    <div class="mensagem-sucesso"><?= htmlspecialchars($sucessoSenha) ?></div>
<?php endif; ?>

<?php if ($erroSenha): ?>
    <div class="mensagem erro"><?= htmlspecialchars($erroSenha) ?></div>
<?php endif; ?>

<form method="POST" action="/admin/perfil/salvar">
    <fieldset>
        <legend>Dados do Perfil</legend>
        <div class="form-row">
            <div class="form-col">
                <label>Nome:
                    <input type="text" name="nome" value="<?= htmlspecialchars($admin['nome'] ?? '') ?>" required>
                </label>
            </div>
            <div class="form-col">
                <label>Email:
                    <input type="email" name="email" value="<?= htmlspecialchars($admin['email'] ?? '') ?>" required>
                </label>
            </div>
        </div>
    </fieldset>

    <div class="form-actions">
        <a href="/admin/representantes" class="btn">Voltar</a>
        <button type="submit" class="btn-primary">Salvar</button>
    </div>
</form>

<?php require __DIR__ . '/../../partials/dashboard_footer.php'; ?>