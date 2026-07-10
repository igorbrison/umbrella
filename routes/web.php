<?php
// Rota inicial
$router->get('/', function() {
    echo 'Sistema rodando!';
});

// Rotas do cliente
$router->get('/cliente', function() {
    $controller = new ClienteController();
    $controller->index();
});

$router->post('/cliente/salvar', function() {
    $controller = new ClienteController();
    $controller->salvar();
});