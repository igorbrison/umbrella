<?php
require_once __DIR__ . '/../Models/Representante.php';

class AuthController {
    public function loginForm(): void {
        require __DIR__ . '/../Views/login.php';
    }

    public function login(): void {
        $cnpj = preg_replace('/\D/', '', $_POST['cnpj'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $model = new Representante();
        $representante = $model->buscarPorCnpj($cnpj);
        if ($representante && password_verify($senha, $representante['senha'])) {
            $_SESSION['representante_id'] = $representante['id'];
            $_SESSION['representante_nome'] = $representante['nome_razao'];
            header('Location: /painel');
            exit;
        } else {
            echo "CNPJ ou senha inválidos. <a href='/login'>Tentar novamente</a>";
        }
    }

    public function logout(): void {
        session_destroy();
        header('Location: /login');
        exit;
    }
}