<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

$router = new \Bramus\Router\Router();

// Carrega as rotas da aplicação
require_once __DIR__ . '/../routes/web.php';

$router->run();