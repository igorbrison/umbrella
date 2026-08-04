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
// Redireciona a raiz do site para a tela de login do representante.
$router->get('/', function() {
    header('Location: /login');
    exit;
});

// ============================================================
// 2. AUTENTICAÇÃO DO ADMINISTRADOR
// ============================================================
// Formulário de login (GET) e processamento (POST).
// Logout encerra a sessão do admin.

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
// O prefixo /admin/representantes agrupa todas as operações de CRUD.
// O middleware AuthAdminMiddleware é executado antes de qualquer requisição
// (GET ou POST) dentro deste grupo, garantindo que só administradores logados
// tenham acesso.

$router->mount('/admin/representantes', function() use ($router) {
    $router->before('GET|POST', '/.*', function() {
        require_once __DIR__ . '/../app/Middlewares/AuthAdminMiddleware.php';
        AuthAdminMiddleware::verificar();
    });

    // Listagem de representantes (com ordenação)
    $router->get('/', function() {
        require_once __DIR__ . '/../app/Controllers/RepresentanteController.php';
        (new RepresentanteController())->index();
    });

    // Exibe formulário em branco para novo representante
    $router->get('/criar', function() {
        require_once __DIR__ . '/../app/Controllers/RepresentanteController.php';
        (new RepresentanteController())->criar();
    });

    // Processa o envio do formulário (inserção ou edição)
    $router->post('/salvar', function() {
        require_once __DIR__ . '/../app/Controllers/RepresentanteController.php';
        (new RepresentanteController())->salvar();
    });

    // Carrega formulário preenchido para edição de um representante específico (parâmetro ID)
    $router->get('/editar/(\d+)', function($id) {
        require_once __DIR__ . '/../app/Controllers/RepresentanteController.php';
        (new RepresentanteController())->editar((int)$id);
    });

    // Alterna o status ativo/inativo de um representante
    $router->get('/status/(\d+)', function($id) {
        require_once __DIR__ . '/../app/Controllers/RepresentanteController.php';
        (new RepresentanteController())->status((int)$id);
    });

    // Exclui permanentemente um representante
    $router->get('/excluir/(\d+)', function($id) {
        require_once __DIR__ . '/../app/Controllers/RepresentanteController.php';
        (new RepresentanteController())->excluir((int)$id);
    });
});

// Redireciona /admin para a lista de representantes (após login)
$router->get('/admin', function() {
    header('Location: /admin/representantes');
    exit;
});

// ============================================================
// 4. AUTENTICAÇÃO DO REPRESENTANTE
// ============================================================
// Login do representante (CNPJ + senha).
// Logout encerra a sessão do representante.

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
// O prefixo /painel agrupa as funcionalidades acessíveis ao representante.
// O middleware inline verifica se existe uma sessão de representante ativa.
// A rota raiz redireciona para a listagem de clientes.

$router->mount('/painel', function() use ($router) {
    // Middleware de proteção (aplica-se a GET e POST)
    $router->before('GET|POST', '/.*', function() {
        if (!isset($_SESSION['representante_id'])) {
            header('Location: /login');
            exit;
        }
    });

    // Redireciona /painel para /painel/clientes (página principal do representante)
    $router->get('/', function() {
        header('Location: /painel/clientes');
        exit;
    });

    // Listagem de clientes do representante logado
    $router->get('/clientes', function() {
        require_once __DIR__ . '/../app/Controllers/ClienteController.php';
        (new ClienteController())->index();
    });

    // Formulário para novo cliente
    $router->get('/clientes/criar', function() {
        require_once __DIR__ . '/../app/Controllers/ClienteController.php';
        (new ClienteController())->criar();
    });

    // Processa o formulário de cliente (inserção ou edição)
    $router->post('/clientes/salvar', function() {
        require_once __DIR__ . '/../app/Controllers/ClienteController.php';
        (new ClienteController())->salvar();
    });

    // Edição de um cliente específico (parâmetro ID)
    $router->get('/clientes/editar/(\d+)', function($id) {
        require_once __DIR__ . '/../app/Controllers/ClienteController.php';
        (new ClienteController())->editar((int)$id);
    });

    // Exclusão de um cliente
    $router->get('/clientes/excluir/(\d+)', function($id) {
        require_once __DIR__ . '/../app/Controllers/ClienteController.php';
        (new ClienteController())->excluir((int)$id);
    });
});

// ============================================================
// 6. RECUPERAÇÃO DE SENHA
// ============================================================
// Fluxo de "esqueci minha senha" para admin e representante.
// As rotas GET exibem os formulários; as POST processam os dados.

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
// 7. API (futura integração com o aplicativo Flutter)
// ============================================================
// Endpoint que retornará os módulos ativos do representante.

$router->get('/api/modulos', function() {
    // A ser implementado
});