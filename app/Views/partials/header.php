<?php
/**
 * Arquivo: Views/partials/header.php
 * Função: Cabeçalho HTML simples para páginas públicas.
 * 
 * Responsável por:
 *   - Gerar a estrutura HTML inicial para telas que NÃO exigem autenticação,
 *     como login, recuperação de senha, etc.
 *   - Incluir o CSS global e o favicon do sistema.
 * 
 * Uso: Incluir no início de cada view pública, após definir a variável $titulo.
 * Exemplo:
 *   $titulo = 'Login Representante';
 *   require __DIR__ . '/../partials/header.php';
 *   // conteúdo da página...
 *   // (não há footer parcial correspondente; feche </body></html> manualmente)
 * 
 * Espera que a variável $titulo esteja definida (para o título da aba).
 * Caso não esteja, usa 'Umbrella Corporation' como fallback.
 */

// Fallback para o título da página
if (!isset($titulo)) {
    $titulo = 'Umbrella Corporation';
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="icon" href="/img/logo-sem-fundo.png" type="image/x-icon">
    <title><?= htmlspecialchars($titulo) ?></title>
</head>
<body>