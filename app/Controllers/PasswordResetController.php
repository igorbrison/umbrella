<?php
require_once __DIR__ . '/../Models/Database.php';

class PasswordResetController {
    private \PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    // Exibe formulário "Esqueci minha senha"
    public function showForgotForm(): void {
        require __DIR__ . '/../Views/password/forgot.php';
    }

    // Processa o envio do link de recuperação
    public function sendResetLink(): void {
        $email = $_POST['email'] ?? '';
        $tipo = $_POST['tipo'] ?? 'representante'; // 'admin' ou 'representante'

        if (empty($email)) {
            echo "Email é obrigatório. <a href='/forgot-password'>Voltar</a>";
            exit;
        }

        // Verifica se o email existe na tabela correta
        $tabela = $tipo === 'admin' ? 'administradores' : 'representantes';
        $stmt = $this->pdo->prepare("SELECT id, email FROM $tabela WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch();

        if (!$usuario) {
            // Não revela que o email não existe (segurança)
            echo "Se o email estiver cadastrado, um link de recuperação foi enviado.";
            exit;
        }

        // Gera token único
        $token = bin2hex(random_bytes(32));
        $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Remove tokens antigos do mesmo email/tipo
        $stmt = $this->pdo->prepare("DELETE FROM password_resets WHERE email = :email AND tipo = :tipo");
        $stmt->execute([':email' => $email, ':tipo' => $tipo]);

        // Insere novo token
        $stmt = $this->pdo->prepare("INSERT INTO password_resets (email, token, tipo, expira_em) VALUES (:email, :token, :tipo, :expira)");
        $stmt->execute([':email' => $email, ':token' => $token, ':tipo' => $tipo, ':expira' => $expira]);

        // Envia email (simples com mail())
        $assunto = "Redefinição de senha";
        $link = "http://umbrella.test/reset-password?token=$token&tipo=$tipo";
        $mensagem = "Clique no link para redefinir sua senha: $link";
        $headers = "From: no-reply@umbrella.test\r\n";

        // Em desenvolvimento, pode não funcionar. Exibimos o link na tela para teste.
        if (mail($email, $assunto, $mensagem, $headers)) {
            echo "Email enviado com sucesso! Verifique sua caixa de entrada.";
        } else {
            // Fallback para desenvolvimento: exibe o link
            echo "Não foi possível enviar o email. Modo desenvolvimento: <a href='$link'>Clique aqui para redefinir</a>";
        }
    }

    // Exibe o formulário de redefinição (após clicar no link)
    public function showResetForm(): void {
        $token = $_GET['token'] ?? '';
        $tipo = $_GET['tipo'] ?? '';
        if (empty($token) || empty($tipo)) {
            echo "Token inválido.";
            exit;
        }

        // Verifica token válido
        $stmt = $this->pdo->prepare("SELECT * FROM password_resets WHERE token = :token AND tipo = :tipo AND expira_em > NOW()");
        $stmt->execute([':token' => $token, ':tipo' => $tipo]);
        $reset = $stmt->fetch();

        if (!$reset) {
            echo "Token inválido ou expirado.";
            exit;
        }

        require __DIR__ . '/../Views/password/reset.php';
    }

    // Processa a nova senha
    public function resetPassword(): void {
        $token = $_POST['token'] ?? '';
        $tipo = $_POST['tipo'] ?? '';
        $senha = $_POST['senha'] ?? '';
        $confirmar = $_POST['confirmar_senha'] ?? '';

        if ($senha !== $confirmar) {
            echo "Senhas não conferem.";
            exit;
        }

        // Busca token válido
        $stmt = $this->pdo->prepare("SELECT * FROM password_resets WHERE token = :token AND tipo = :tipo AND expira_em > NOW()");
        $stmt->execute([':token' => $token, ':tipo' => $tipo]);
        $reset = $stmt->fetch();

        if (!$reset) {
            echo "Token inválido ou expirado.";
            exit;
        }

        $email = $reset['email'];
        $tabela = $tipo === 'admin' ? 'administradores' : 'representantes';
        $hash = password_hash($senha, PASSWORD_DEFAULT);

        // Atualiza a senha
        $stmt = $this->pdo->prepare("UPDATE $tabela SET senha = :senha WHERE email = :email");
        $stmt->execute([':senha' => $hash, ':email' => $email]);

        // Remove token usado
        $stmt = $this->pdo->prepare("DELETE FROM password_resets WHERE token = :token");
        $stmt->execute([':token' => $token]);

        echo "Senha redefinida com sucesso! <a href='/login'>Faça login</a>";
    }
}