<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Painel do Representante</title>
</head>
<body>
    <h1>Bem-vindo, <?= htmlspecialchars($_SESSION['representante_nome'] ?? 'Representante') ?></h1>
    <p>Área do representante em construção.</p>
    <a href="/logout">Sair</a>
</body>
</html>