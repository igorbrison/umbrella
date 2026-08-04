<?php
session_start();

// --- ROTAS PÚBLICAS ---
$router->get('/', function() {
    header('Location: /login');
    exit;
});

// --- LOGIN DO ADMIN ---
$router->get('/admin/login', function() {
    require_once __DIR__ . '/../app/Controllers/AdminAuthController.php';
    (new AdminAuthController())->loginForm();
});
$router->post('/admin/login', function() {
    require_once __DIR__ . '/../app/Controllers/AdminAuthController.php';
    (new AdminAuthController())->login();
});
$router->get('/admin/logout', function() {
    require_once __DIR__ . '/../app/Controllers/AdminAuthController.php';
    (new AdminAuthController())->logout();
});

// --- ÁREA ADMINISTRATIVA (PROTEGIDA) ---
$router->mount('/admin/representantes', function() use ($router) {
    $router->before('GET|POST', '/.*', function() {
        require_once __DIR__ . '/../app/Middlewares/AuthAdminMiddleware.php';
        AuthAdminMiddleware::verificar();
    });

    $router->get('/', function() {
        require_once __DIR__ . '/../app/Controllers/RepresentanteController.php';
        (new RepresentanteController())->index();
    });
    $router->get('/criar', function() {
        require_once __DIR__ . '/../app/Controllers/RepresentanteController.php';
        (new RepresentanteController())->criar();
    });
    $router->post('/salvar', function() {
        require_once __DIR__ . '/../app/Controllers/RepresentanteController.php';
        (new RepresentanteController())->salvar();
    });
    $router->get('/editar/(\d+)', function($id) {
        require_once __DIR__ . '/../app/Controllers/RepresentanteController.php';
        (new RepresentanteController())->editar((int)$id);
    });
    $router->get('/status/(\d+)', function($id) {
        require_once __DIR__ . '/../app/Controllers/RepresentanteController.php';
        (new RepresentanteController())->status((int)$id);
    });
    $router->get('/excluir/(\d+)', function($id) {
        require_once __DIR__ . '/../app/Controllers/RepresentanteController.php';
        (new RepresentanteController())->excluir((int)$id);
    });
});

$router->get('/admin', function() {
    header('Location: /admin/representantes');
    exit;
});

// --- LOGIN DO REPRESENTANTE ---
$router->get('/login', function() {
    require_once __DIR__ . '/../app/Controllers/AuthController.php';
    (new AuthController())->loginForm();
});
$router->post('/login', function() {
    require_once __DIR__ . '/../app/Controllers/AuthController.php';
    (new AuthController())->login();
});
$router->get('/logout', function() {
    require_once __DIR__ . '/../app/Controllers/AuthController.php';
    (new AuthController())->logout();
});

// --- ÁREA DO REPRESENTANTE (PROTEGIDA) ---
$router->mount('/painel', function() use ($router) {
    $router->before('GET', '/.*', function() {
        if (!isset($_SESSION['representante_id'])) {
            header('Location: /login');
            exit;
        }
    });

    $router->get('/', function() {
        require __DIR__ . '/../app/Views/painel/index.php';
    });
});

// --- RECUPERAÇÃO DE SENHA ---
$router->get('/forgot-password', function() {
    require_once __DIR__ . '/../app/Controllers/PasswordResetController.php';
    (new PasswordResetController())->showForgotForm();
});
$router->post('/forgot-password', function() {
    require_once __DIR__ . '/../app/Controllers/PasswordResetController.php';
    (new PasswordResetController())->sendResetLink();
});
$router->get('/reset-password', function() {
    require_once __DIR__ . '/../app/Controllers/PasswordResetController.php';
    (new PasswordResetController())->showResetForm();
});
$router->post('/reset-password', function() {
    require_once __DIR__ . '/../app/Controllers/PasswordResetController.php';
    (new PasswordResetController())->resetPassword();
});

// --- API ---
$router->get('/api/modulos', function() {
    // futuramente
});