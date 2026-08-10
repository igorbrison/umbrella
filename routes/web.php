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

    // Comissões
    $router->get('/comissoes', function() {
        require_once __DIR__ . '/../app/Controllers/RepresentanteController.php';
        (new RepresentanteController())->comissoes();
    });

    // Relatório de comissão de um representante
    $router->get('/comissoes/relatorio/(\d+)', function($id) {
        require_once __DIR__ . '/../app/Controllers/RepresentanteController.php';
        (new RepresentanteController())->relatorioComissao((int)$id);
    });

    // Editar observação de pagamento de comissão
    $router->post('/comissoes/editar-observacao', function() {
        require_once __DIR__ . '/../app/Controllers/RepresentanteController.php';
        (new RepresentanteController())->editarObservacao();
    });

    // Registrar pagamento de comissão
    $router->post('/pagar-comissao', function() {
        require_once __DIR__ . '/../app/Controllers/RepresentanteController.php';
        (new RepresentanteController())->pagarComissao();
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

// --- Clientes (admin) – listagem e ações de licença ---
$router->mount('/admin/clientes', function() use ($router) {
    $router->before('GET|POST', '/.*', function() {
        require_once __DIR__ . '/../app/Middlewares/AuthAdminMiddleware.php';
        AuthAdminMiddleware::verificar();
    });

    $router->get('/', function() {
        require_once __DIR__ . '/../app/Controllers/AdminLicencaController.php';
        (new AdminLicencaController())->index();
    });

    $router->get('/editar/(\d+)', function($id) {
        require_once __DIR__ . '/../app/Controllers/AdminClienteController.php';
        (new AdminClienteController())->editar((int)$id);
    });

    $router->post('/salvar', function() {
        require_once __DIR__ . '/../app/Controllers/AdminClienteController.php';
        (new AdminClienteController())->salvar();
    });

    $router->get('/renovar/(\d+)', function($id) {
        require_once __DIR__ . '/../app/Controllers/AdminLicencaController.php';
        (new AdminLicencaController())->renovar((int)$id);
    });

    $router->get('/gerar-token/(\d+)', function($id) {
        require_once __DIR__ . '/../app/Controllers/AdminLicencaController.php';
        (new AdminLicencaController())->gerarTokenOffline((int)$id);
    });

    $router->post('/pagar', function() {
        require_once __DIR__ . '/../app/Controllers/AdminLicencaController.php';
        (new AdminLicencaController())->pagar();
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

// ===== ROTAS DE PERFIL E SENHA (ADMIN) =====
$router->get('/admin/perfil', function() {
    require_once __DIR__ . '/../app/Middlewares/AuthAdminMiddleware.php';
    AuthAdminMiddleware::verificar();
    require_once __DIR__ . '/../app/Controllers/AdminPerfilController.php';
    (new AdminPerfilController())->editar();
});

$router->post('/admin/perfil/salvar', function() {
    require_once __DIR__ . '/../app/Middlewares/AuthAdminMiddleware.php';
    AuthAdminMiddleware::verificar();
    require_once __DIR__ . '/../app/Controllers/AdminPerfilController.php';
    (new AdminPerfilController())->salvar();
});

$router->post('/admin/alterar-senha', function() {
    require_once __DIR__ . '/../app/Middlewares/AuthAdminMiddleware.php';
    AuthAdminMiddleware::verificar();
    require_once __DIR__ . '/../app/Controllers/SenhaController.php';
    (new SenhaController())->alterar('admin');
});

// ===== SOLICITAÇÕES (ADMIN) =====
$router->get('/admin/solicitacoes', function() {
    require_once __DIR__ . '/../app/Middlewares/AuthAdminMiddleware.php';
    AuthAdminMiddleware::verificar();
    require_once __DIR__ . '/../app/Controllers/AdminSolicitacaoController.php';
    (new AdminSolicitacaoController())->index();
});

$router->post('/admin/solicitacoes/atualizar', function() {
    require_once __DIR__ . '/../app/Middlewares/AuthAdminMiddleware.php';
    AuthAdminMiddleware::verificar();
    require_once __DIR__ . '/../app/Controllers/AdminSolicitacaoController.php';
    (new AdminSolicitacaoController())->responder();
});

// ===== COBRANÇA AUTOMÁTICA (ADMIN) =====
$router->post('/admin/cobranca/enviar', function() {
    require_once __DIR__ . '/../app/Middlewares/AuthAdminMiddleware.php';
    AuthAdminMiddleware::verificar();

    $_GET['forcar'] = '1';

    ob_start();
    require __DIR__ . '/../cron/cobranca.php';
    $output = ob_get_clean();

    $_SESSION['cobranca_output'] = $output;
    header('Location: /admin/clientes');
    exit;
});

// ===== RELATÓRIOS (ADMIN) =====
$router->get('/admin/relatorios/pagamentos', function() {
    require_once __DIR__ . '/../app/Middlewares/AuthAdminMiddleware.php';
    AuthAdminMiddleware::verificar();
    require_once __DIR__ . '/../app/Controllers/RelatorioController.php';
    (new RelatorioController())->admin();
});

// Redireciona /admin para a lista de representantes
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

    $router->get('/perfil', function() {
        require_once __DIR__ . '/../app/Controllers/RepresentantePerfilController.php';
        (new RepresentantePerfilController())->editar();
    });

    $router->post('/perfil/salvar', function() {
        require_once __DIR__ . '/../app/Controllers/RepresentantePerfilController.php';
        (new RepresentantePerfilController())->salvar();
    });

    $router->post('/alterar-senha', function() {
        require_once __DIR__ . '/../app/Controllers/SenhaController.php';
        (new SenhaController())->alterar('representante');
    });

    $router->get('/solicitacoes', function() {
        require_once __DIR__ . '/../app/Controllers/SolicitacaoController.php';
        (new SolicitacaoController())->index();
    });

    $router->post('/solicitacoes/enviar', function() {
        require_once __DIR__ . '/../app/Controllers/SolicitacaoController.php';
        (new SolicitacaoController())->enviar();
    });

    $router->get('/solicitacoes/editar/(\d+)', function($id) {
        require_once __DIR__ . '/../app/Controllers/SolicitacaoController.php';
        (new SolicitacaoController())->editar((int)$id);
    });

    $router->post('/solicitacoes/atualizar', function() {
        require_once __DIR__ . '/../app/Controllers/SolicitacaoController.php';
        (new SolicitacaoController())->atualizar();
    });

    $router->get('/solicitacoes/ver/(\d+)', function($id) {
        require_once __DIR__ . '/../app/Controllers/SolicitacaoController.php';
        (new SolicitacaoController())->ver((int)$id);
    });

    // ===== RELATÓRIOS (REPRESENTANTE) =====
    $router->get('/relatorios/pagamentos', function() {
        require_once __DIR__ . '/../app/Controllers/RelatorioController.php';
        (new RelatorioController())->painel();
    });
});

// ============================================================
// 6. ROTA DO DASHBOARD (PÁGINA DE INDICADORES)
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
// 8. API DE LICENCIAMENTO E INTEGRAÇÃO
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

$router->get('/api/modulos', function() {
    require_once __DIR__ . '/../app/Controllers/ApiController.php';
    (new ApiController())->listarModulos();
});

$router->post('/api/login', function() {
    require_once __DIR__ . '/../app/Controllers/ApiController.php';
    (new ApiController())->login();
});