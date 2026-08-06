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
    /**
     * Exibe a view com o formulário de login do representante.
     * Rota associada: GET /login
     */
    public function loginForm(): void {
        require __DIR__ . '/../Views/login.php';
    }

    // ============================================================
    // 2. PROCESSAR LOGIN
    // ============================================================
    /**
     * Processa o envio do formulário de login do representante.
     * 
     * Fluxo:
     *   - Remove caracteres não numéricos do CNPJ informado.
     *   - Busca o representante no banco de dados pelo CNPJ.
     *   - Verifica a senha usando password_verify (hash bcrypt).
     *   - Se válido, armazena os dados na sessão e redireciona para /painel.
     *   - Caso contrário, exibe mensagem de erro.
     * 
     * O nome exibido no painel será o nome_exibicao (se cadastrado)
     * ou a razão social como fallback.
     * 
     * Rota associada: POST /login
     */
    public function login(): void {
        // Remove caracteres não numéricos do CNPJ (pontos, barras, traços)
        $cnpj = preg_replace('/\D/', '', $_POST['cnpj'] ?? '');
        $senha = $_POST['senha'] ?? '';
        
        // Busca o representante pelo CNPJ
        $model = new Representante();
        $representante = $model->buscarPorCnpj($cnpj);

        // Verifica se o representante existe e a senha está correta
        if ($representante && password_verify($senha, $representante['senha'])) {
            unset($_SESSION['admin_id'], $_SESSION['admin_nome'], $_SESSION['admin_email']);
            $_SESSION['representante_id'] = $representante['id'];
            $_SESSION['representante_nome'] = $representante['nome_exibicao'] ?: $representante['nome_razao'];
            $_SESSION['representante_email'] = $representante['email'];   // ← adicione esta linha
            header('Location: /painel');
            exit;
        } else {
            // Credenciais inválidas
            echo "CNPJ ou senha inválidos. <a href='/login'>Tentar novamente</a>";
        }
    }

    // ============================================================
    // 3. LOGOUT
    // ============================================================
    /**
     * Encerra a sessão do representante e redireciona para o login.
     * 
     * Realiza limpeza completa:
     *   - Esvazia o array $_SESSION.
     *   - Invalida o cookie de sessão (se existir).
     *   - Destrói a sessão no servidor.
     * 
     * Rota associada: GET /logout
     */
    public function logout(): void {
        // Limpa todas as variáveis de sessão
        $_SESSION = [];
        
        // Se houver cookie de sessão, invalida-o
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destrói a sessão no servidor
        session_destroy();
        header('Location: /login');
        exit;
    }
}