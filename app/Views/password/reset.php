<!DOCTYPE html>
<html>
<head><title>Redefinir senha</title></head>
<body>
    <h1>Nova senha</h1>
    <form method="POST" action="/reset-password">
        <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">
        <input type="hidden" name="tipo" value="<?= htmlspecialchars($_GET['tipo']) ?>">
        <label>Nova senha: <input type="password" name="senha" required></label><br>
        <label>Confirmar senha: <input type="password" name="confirmar_senha" required></label><br>
        <button type="submit">Redefinir senha</button>
    </form>
</body>
</html>