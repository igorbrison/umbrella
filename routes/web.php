<?php

/**
 * Arquivo: routes/web.php
 * Função: Define todas as rotas da aplicação (URLs acessíveis e seus respectivos controladores).
 * 
 * Observações:
 *   - A sessão é iniciada uma única vez no topo deste arquivo.
 *   - As rotas estão organizadas por funcionalidade (admin, representante, recuperação de senha, API).
 *   - Middlewares inline protegem rotas que exigem autenticação.
 */

session_start();

// ============================================================
// 1. ROTA PÚBLICA INICIAL
// ============================================================
$router->get('/', function() {
    header('Location: /login');
    exit;
});

// ============================================================
// 2. AUTENTICAÇÃO DO ADMINISTRADOR
// ============================================================
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

// ============================================================
// 3. ÁREA ADMINISTRATIVA (PROTEGIDA)
// ============================================================

// --- Representantes ---
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

// --- Módulos (admin) ---
$router->mount('/admin/modulos', function() use ($router) {
    $router->before('GET|POST', '/.*', function() {
        require_once __DIR__ . '/../app/Middlewares/AuthAdminMiddleware.php';
        AuthAdminMiddleware::verificar();
    });

    $router->get('/', function() {
        require_once __DIR__ . '/../app/Controllers/ModuloController.php';
        (new ModuloController())->index();
    });

    $router->get('/criar', function() {
        require_once __DIR__ . '/../app/Controllers/ModuloController.php';
        (new ModuloController())->criar();
    });

    $router->post('/salvar', function() {
        require_once __DIR__ . '/../app/Controllers/ModuloController.php';
        (new ModuloController())->salvar();
    });

    $router->get('/editar/(\d+)', function($id) {
        require_once __DIR__ . '/../app/Controllers/ModuloController.php';
        (new ModuloController())->editar((int)$id);
    });

    $router->get('/excluir/(\d+)', function($id) {
        require_once __DIR__ . '/../app/Controllers/ModuloController.php';
        (new ModuloController())->excluir((int)$id);
    });
});

// --- Licenças (admin) ---
$router->mount('/admin/licencas', function() use ($router) {
    $router->before('GET|POST', '/.*', function() {
        require_once __DIR__ . '/../app/Middlewares/AuthAdminMiddleware.php';
        AuthAdminMiddleware::verificar();
    });

    $router->get('/', function() {
        require_once __DIR__ . '/../app/Controllers/AdminLicencaController.php';
        (new AdminLicencaController())->index();
    });

    $router->get('/renovar/(\d+)', function($id) {
        require_once __DIR__ . '/../app/Controllers/AdminLicencaController.php';
        (new AdminLicencaController())->renovar((int)$id);
    });

    $router->get('/gerar-token/(\d+)', function($id) {
        require_once __DIR__ . '/../app/Controllers/AdminLicencaController.php';
        (new AdminLicencaController())->gerarTokenOffline((int)$id);
    });
});

// --- Clientes (admin) - Edição de clientes ---
$router->mount('/admin/clientes', function() use ($router) {
    $router->before('GET|POST', '/.*', function() {
        require_once __DIR__ . '/../app/Middlewares/AuthAdminMiddleware.php';
        AuthAdminMiddleware::verificar();
    });

    $router->get('/editar/(\d+)', function($id) {
        require_once __DIR__ . '/../app/Controllers/AdminClienteController.php';
        (new AdminClienteController())->editar((int)$id);
    });

    $router->post('/salvar', function() {
        require_once __DIR__ . '/../app/Controllers/AdminClienteController.php';
        (new AdminClienteController())->salvar();
    });
});

// --- Configuração (admin) ---
$router->get('/admin/configuracao', function() {
    require_once __DIR__ . '/../app/Middlewares/AuthAdminMiddleware.php';
    AuthAdminMiddleware::verificar();
    require_once __DIR__ . '/../app/Controllers/ConfiguracaoController.php';
    (new ConfiguracaoController())->index();
});

$router->post('/admin/configuracao/salvar', function() {
    require_once __DIR__ . '/../app/Middlewares/AuthAdminMiddleware.php';
    AuthAdminMiddleware::verificar();
    require_once __DIR__ . '/../app/Controllers/ConfiguracaoController.php';
    (new ConfiguracaoController())->salvar();
});

// Redireciona /admin para a lista de representantes (após login)
$router->get('/admin', function() {
    header('Location: /admin/representantes');
    exit;
});

// ============================================================
// 4. AUTENTICAÇÃO DO REPRESENTANTE
// ============================================================
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

// ============================================================
// 5. ÁREA DO REPRESENTANTE (PROTEGIDA)
// ============================================================
$router->mount('/painel', function() use ($router) {
    $router->before('GET|POST', '/.*', function() {
        if (!isset($_SESSION['representante_id'])) {
            header('Location: /login');
            exit;
        }
    });

    $router->get('/', function() {
        header('Location: /painel/clientes');
        exit;
    });

    // --- Clientes ---
    $router->get('/clientes', function() {
        require_once __DIR__ . '/../app/Controllers/ClienteController.php';
        (new ClienteController())->index();
    });

    $router->get('/clientes/criar', function() {
        require_once __DIR__ . '/../app/Controllers/ClienteController.php';
        (new ClienteController())->criar();
    });

    $router->post('/clientes/salvar', function() {
        require_once __DIR__ . '/../app/Controllers/ClienteController.php';
        (new ClienteController())->salvar();
    });

    $router->get('/clientes/editar/(\d+)', function($id) {
        require_once __DIR__ . '/../app/Controllers/ClienteController.php';
        (new ClienteController())->editar((int)$id);
    });
});

// ============================================================
// 6. ROTA DO DASHBOARD (REDIRECIONA CONFORME PERFIL)
// ============================================================
$router->get('/dashboard', function() {
    require_once __DIR__ . '/../app/Controllers/DashboardController.php';
    (new DashboardController())->index();
});

// ============================================================
// 7. RECUPERAÇÃO DE SENHA
// ============================================================
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

// ============================================================
// 8. API DE LICENCIAMENTO
// ============================================================
$router->post('/api/licenca/validar', function() {
    require_once __DIR__ . '/../app/Controllers/ApiController.php';
    (new ApiController())->validarLicenca();
});

$router->post('/api/licenca/gerar-token-offline', function() {
    require_once __DIR__ . '/../app/Controllers/ApiController.php';
    (new ApiController())->gerarTokenOffline();
});

$router->post('/api/licenca/validar-renovacao', function() {
    require_once __DIR__ . '/../app/Controllers/ApiController.php';
    (new ApiController())->validarRenovacaoOffline();
});

// ============================================================
// 9. FUTURA INTEGRAÇÃO COM FLUTTER
// ============================================================
$router->get('/api/modulos', function() {
    // A ser implementado
});