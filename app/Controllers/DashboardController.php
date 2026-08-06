<?php
/**
 * Arquivo: Controllers/DashboardController.php
 * Função: Controlador da página inicial do dashboard.
 * 
 * Responsável por redirecionar o usuário autenticado para a
 * página inicial correta conforme seu perfil:
 *   - Administrador → /admin/representantes
 *   - Representante → /painel/clientes
 * 
 * Se nenhum usuário estiver logado, redireciona para /login.
 * 
 * Uso: Rota associada → GET /dashboard
 */

class DashboardController {
    
    /**
     * Redireciona para a página inicial adequada ao perfil do usuário.
     */
    public function index(): void {
        if (isset($_SESSION['admin_id'])) {
            header('Location: /admin/representantes');
        } elseif (isset($_SESSION['representante_id'])) {
            header('Location: /painel/clientes');
        } else {
            header('Location: /login');
        }
        exit;
    }
}