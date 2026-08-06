<?php
/**
 * Arquivo: Middlewares/AuthAdminMiddleware.php
 * Função: Middleware de autenticação para rotas administrativas.
 * 
 * Responsável por:
 *   - Verificar se o administrador está autenticado (sessão ativa).
 *   - Redirecionar para a tela de login do admin caso a sessão não exista.
 *   - Ser executado antes das rotas protegidas do painel administrativo.
 * 
 * Uso: Chamado nas rotas do grupo /admin via método estático.
 * Exemplo:
 *   $router->before('GET|POST', '/.*', function() {
 *       require_once __DIR__ . '/../app/Middlewares/AuthAdminMiddleware.php';
 *       AuthAdminMiddleware::verificar();
 *   });
 * 
 * A sessão ($_SESSION) já deve estar iniciada pelo arquivo routes/web.php.
 */

class AuthAdminMiddleware {
    
    /**
     * Método estático que verifica a existência da sessão de administrador.
     * Se não houver sessão ativa, redireciona para a página de login do admin.
     */
    public static function verificar(): void {
        if (!isset($_SESSION['admin_id'])) {
            header('Location: /admin/login');
            exit;
        }
    }
}