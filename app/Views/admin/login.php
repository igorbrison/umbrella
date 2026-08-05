<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login Administrativo</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <h1>Área Administrativa</h1>
    <form method="POST" action="/admin/login">
        <label>Email: <input type="email" name="email" required></label><br>
        <label>Senha: <input type="password" name="senha" required></label><br>
        <button type="submit">Entrar</button>
    </form>
    <p><a href="/forgot-password">Esqueci minha senha</a></p>
</body>
</html>