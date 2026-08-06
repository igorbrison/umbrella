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
    // 2. PROCESSAR LOGIN
    // ============================================================
    /**
     * Processa o envio do formulário de login.
     * Verifica as credenciais no banco de dados (tabela administradores).
     * Se válidas, cria as variáveis de sessão e redireciona para o painel.
     * Caso contrário, exibe mensagem de erro.
     * 
     * Rota associada: POST /admin/login
     */
    public function login(): void {
        // Captura os dados do formulário
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';
        
        // Busca o administrador pelo email
        $model = new Admin();
        $admin = $model->buscarPorEmail($email);
        
        // Verifica se o admin existe e a senha está correta (hash bcrypt)
        if ($admin && password_verify($senha, $admin['senha'])) {
            // Armazena dados na sessão
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_nome'] = $admin['nome'];
            // Redireciona para a lista de representantes
            header('Location: /admin/representantes');
            exit;
        } else {
            // Credenciais inválidas
            echo "Email ou senha inválidos. <a href='/admin/login'>Tentar novamente</a>";
        }
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