<?php
require_once __DIR__ . '/../Models/Admin.php';

class AdminAuthController {
    // Exibe o formulário de login
    public function loginForm(): void {
        require __DIR__ . '/../Views/admin/login.php';
    }

    // Processa o login
    public function login(): void {
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';
        $model = new Admin();
        $admin = $model->buscarPorEmail($email);
        if ($admin && password_verify($senha, $admin['senha'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_nome'] = $admin['nome'];
            header('Location: /admin/representantes');
            exit;
        } else {
            echo "Email ou senha inválidos. <a href='/admin/login'>Tentar novamente</a>";
        }
    }

    // Logout
    public function logout(): void {
        session_destroy();
        header('Location: /admin/login');
        exit;
    }
}