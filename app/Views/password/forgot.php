<!DOCTYPE html>
<html>
<head><title>Esqueci minha senha</title></head>
<body>
    <h1>Recuperar senha</h1>
    <form method="POST" action="/forgot-password">
        <label>Email: <input type="email" name="email" required></label><br>
        <label>Tipo de usuário:
            <select name="tipo">
                <option value="representante">Representante</option>
                <option value="admin">Administrador</option>
            </select>
        </label><br>
        <button type="submit">Enviar link de recuperação</button>
    </form>
</body>
</html>