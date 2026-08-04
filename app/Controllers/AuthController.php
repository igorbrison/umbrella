<?php
require_once __DIR__ . '/../Models/Representante.php';

class AuthController {
    // Exibe o formulário de login do representante
    public function loginForm(): void {
        require __DIR__ . '/../Views/login.php';
    }

    // Processa o login
    public function login(): void {
        // Remove caracteres não numéricos do CNPJ
        $cnpj = preg_replace('/\D/', '', $_POST['cnpj'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $model = new Representante();
        $representante = $model->buscarPorCnpj($cnpj);

        if ($representante && password_verify($senha, $representante['senha'])) {
            // Armazena dados na sessão
            $_SESSION['representante_id'] = $representante['id'];
            // Usa o nome de exibição se estiver preenchido; caso contrário, a razão social
            $_SESSION['representante_nome'] = $representante['nome_exibicao'] ?: $representante['nome_razao'];
            header('Location: /painel');
            exit;
        } else {
            echo "CNPJ ou senha inválidos. <a href='/login'>Tentar novamente</a>";
        }
    }

    // Encerra a sessão do representante
    public function logout(): void {
        // session_destroy() sozinho não limpa a superglobal; vamos limpar explicitamente
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