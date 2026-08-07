<?php
/**
 * Arquivo: Controllers/AuthController.php
 * Função: Controlador de autenticação do representante.
 * 
 * Responsável por:
 *   - Exibir o formulário de login do representante.
 *   - Processar as credenciais (CNPJ e senha) e autenticar o representante.
 *   - Encerrar a sessão do representante (logout) com limpeza completa dos cookies.
 * 
 * A sessão ($_SESSION) já deve estar iniciada pelo arquivo routes/web.php.
 * Após login bem-sucedido, armazena:
 *   - $_SESSION['representante_id']
 *   - $_SESSION['representante_nome'] (usa nome_exibicao, se definido; senão, razão social)
 */

require_once __DIR__ . '/../Models/Representante.php';

class AuthController {
    
    // ============================================================
    // 1. EXIBIR FORMULÁRIO DE LOGIN
    // ============================================================
    public function loginForm(): void {
        require __DIR__ . '/../Views/login.php';
    }

    // ============================================================
    // 2. PROCESSAR LOGIN (resposta JSON)
    // ============================================================
    public function login(): void {
        $cnpj = preg_replace('/\D/', '', $_POST['cnpj'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $model = new Representante();
        $representante = $model->buscarPorCnpj($cnpj);

        if ($representante && password_verify($senha, $representante['senha'])) {
            unset($_SESSION['admin_id'], $_SESSION['admin_nome'], $_SESSION['admin_email']);
            $_SESSION['representante_id'] = $representante['id'];
            $_SESSION['representante_nome'] = $representante['nome_exibicao'] ?: $representante['nome_razao'];
            $_SESSION['representante_email'] = $representante['email'] ?? '';
            echo json_encode(['sucesso' => true, 'redirect' => '/painel']);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => 'CNPJ ou senha inválidos.']);
        }
        exit;
    }

    // ============================================================
    // 3. LOGOUT
    // ============================================================
    public function logout(): void {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        header('Location: /login');
        exit;
    }
}