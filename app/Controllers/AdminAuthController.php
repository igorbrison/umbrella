<?php
/**
 * Arquivo: Controllers/AdminAuthController.php
 * Função: Controlador de autenticação do administrador.
 * 
 * Responsável por:
 *   - Exibir o formulário de login do painel administrativo.
 *   - Processar as credenciais (email e senha) e autenticar o administrador.
 *   - Encerrar a sessão do administrador (logout).
 * 
 * A sessão ($_SESSION) já deve estar iniciada pelo arquivo routes/web.php.
 * Após login bem-sucedido, armazena:
 *   - $_SESSION['admin_id']
 *   - $_SESSION['admin_nome']
 *   - $_SESSION['admin_email']
 */

require_once __DIR__ . '/../Models/Admin.php';

class AdminAuthController {
    
    // ============================================================
    // 1. EXIBIR FORMULÁRIO DE LOGIN
    // ============================================================
    /**
     * Exibe a view com o formulário de login do administrador.
     * Rota associada: GET /admin/login
     */
    public function loginForm(): void {
        require __DIR__ . '/../Views/admin/login.php';
    }

    // ============================================================
    // 2. PROCESSAR LOGIN (resposta JSON)
    // ============================================================
    /**
     * Processa o envio do formulário de login.
     * Retorna JSON com sucesso/erro em vez de redirecionar.
     * 
     * Rota associada: POST /admin/login
     */
    public function login(): void {
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';
        $model = new Admin();
        $admin = $model->buscarPorEmail($email);

        if ($admin && password_verify($senha, $admin['senha'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_nome'] = $admin['nome'];
            $_SESSION['admin_email'] = $admin['email'];
            echo json_encode(['sucesso' => true, 'redirect' => '/admin/representantes']);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => 'Email ou senha inválidos.']);
        }
        exit;
    }

    // ============================================================
    // 3. LOGOUT
    // ============================================================
    /**
     * Encerra a sessão do administrador e redireciona para o login.
     * Rota associada: GET /admin/logout
     */
    public function logout(): void {
        session_destroy();
        header('Location: /admin/login');
        exit;
    }
}