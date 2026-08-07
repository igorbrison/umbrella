<?php
/**
 * Arquivo: Controllers/PasswordResetController.php
 * Função: Controlador de recuperação e redefinição de senha.
 * 
 * Responsável por:
 *   - Exibir o formulário "Esqueci minha senha" (informar email e tipo de usuário).
 *   - Gerar um token único de redefinição e armazená-lo no banco de dados.
 *   - Enviar o link de redefinição por email (utiliza o helper Email com PHPMailer).
 *   - Exibir o formulário de redefinição de senha (após validação do token).
 *   - Processar a nova senha e atualizá-la no banco de dados (admin ou representante).
 * 
 * Fluxo completo:
 *   1. Usuário acessa /forgot-password e informa email + tipo.
 *   2. Sistema gera token, salva em password_resets e envia link por e-mail.
 *   3. Usuário clica no link (/reset-password?token=...&tipo=...).
 *   4. Sistema valida o token e exibe formulário de nova senha.
 *   5. Usuário define nova senha e sistema atualiza no banco.
 * 
 * Acesso: Rotas públicas (não exigem autenticação).
 */

require_once __DIR__ . '/../Models/Database.php';
require_once __DIR__ . '/../Helpers/Email.php';

class PasswordResetController {
    
    /**
     * @var \PDO $pdo
     * Instância do PDO para executar consultas SQL.
     */
    private \PDO $pdo;

    /**
     * Construtor da classe.
     * Obtém a instância única do banco de dados via Database::getInstance().
     */
    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    // ============================================================
    // 1. EXIBIR FORMULÁRIO "ESQUECI MINHA SENHA"
    // ============================================================
    /**
     * Exibe a view com o formulário onde o usuário informa seu email
     * e seleciona o tipo de usuário (admin ou representante).
     * Rota associada: GET /forgot-password
     */
    public function showForgotForm(): void {
        require __DIR__ . '/../Views/password/forgot.php';
    }

    // ============================================================
    // 2. PROCESSAR ENVIO DO LINK DE RECUPERAÇÃO
    // ============================================================
    /**
     * Processa o formulário "Esqueci minha senha".
     * 
     * Fluxo:
     *   - Valida se o email foi informado.
     *   - Verifica se o email existe na tabela correspondente (admin ou representante).
     *   - Gera um token único (64 caracteres hexadecimais) com validade de 1 hora.
     *   - Remove tokens antigos do mesmo email/tipo.
     *   - Insere o novo token na tabela password_resets.
     *   - Envia o link de redefinição por email usando o helper Email (PHPMailer).
     *   - Em caso de falha no envio, exibe o link na tela como fallback.
     * 
     * SEGURANÇA: Não revela se o email existe ou não (mensagem genérica).
     * 
     * Rota associada: POST /forgot-password
     */
    public function sendResetLink(): void {
        $email = $_POST['email'] ?? '';
        $tipo = $_POST['tipo'] ?? 'representante'; // 'admin' ou 'representante'

        // Valida se o email foi informado
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
            // Mensagem genérica: não revela se o email existe (segurança)
            echo "Se o email estiver cadastrado, um link de recuperação foi enviado.";
            exit;
        }

        // Gera token único e define expiração (1 hora)
        $token = bin2hex(random_bytes(32));
        $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Remove tokens antigos do mesmo email/tipo (evita acúmulo)
        $stmt = $this->pdo->prepare("DELETE FROM password_resets WHERE email = :email AND tipo = :tipo");
        $stmt->execute([':email' => $email, ':tipo' => $tipo]);

        // Insere o novo token no banco
        $stmt = $this->pdo->prepare("INSERT INTO password_resets (email, token, tipo, expira_em) VALUES (:email, :token, :tipo, :expira)");
        $stmt->execute([':email' => $email, ':token' => $token, ':tipo' => $tipo, ':expira' => $expira]);

        // Prepara o e-mail de redefinição
        $assunto = "Redefinição de senha - Umbrella Corporation";
        $link = "http://umbrella.test/reset-password?token=$token&tipo=$tipo";
        $mensagem = "Olá,\n\n";
        $mensagem .= "Você solicitou a redefinição de sua senha.\n\n";
        $mensagem .= "Clique no link abaixo para criar uma nova senha:\n";
        $mensagem .= "$link\n\n";
        $mensagem .= "Este link é válido por 1 hora.\n\n";
        $mensagem .= "Se você não solicitou esta alteração, ignore este e-mail.\n\n";
        $mensagem .= "Atenciosamente,\nEquipe Umbrella Corporation";

        // Tenta enviar o e-mail utilizando o helper Email (PHPMailer)
        $enviado = Email::enviar($email, $assunto, $mensagem);

        if ($enviado) {
            echo "Email enviado com sucesso! Verifique sua caixa de entrada.";
        } else {
            // Fallback para ambiente de desenvolvimento: exibe o link na tela
            echo "Não foi possível enviar o e-mail automaticamente.";
            echo "<br><br><strong>Modo desenvolvimento:</strong><br>";
            echo "<a href='$link'>Clique aqui para redefinir sua senha</a>";
        }
    }

    // ============================================================
    // 3. EXIBIR FORMULÁRIO DE REDEFINIÇÃO DE SENHA
    // ============================================================
    /**
     * Exibe o formulário de redefinição de senha.
     * Valida se o token recebido via GET é válido e não expirou.
     * 
     * @uses $_GET['token'] Token de redefinição
     * @uses $_GET['tipo']  Tipo de usuário (admin ou representante)
     * 
     * Rota associada: GET /reset-password
     */
    public function showResetForm(): void {
        $token = $_GET['token'] ?? '';
        $tipo = $_GET['tipo'] ?? '';
        
        // Valida se token e tipo foram informados
        if (empty($token) || empty($tipo)) {
            echo "Token inválido.";
            exit;
        }

        // Verifica se o token existe e não expirou
        $stmt = $this->pdo->prepare("SELECT * FROM password_resets WHERE token = :token AND tipo = :tipo AND expira_em > NOW()");
        $stmt->execute([':token' => $token, ':tipo' => $tipo]);
        $reset = $stmt->fetch();

        if (!$reset) {
            echo "Token inválido ou expirado.";
            exit;
        }

        // Carrega a view de redefinição (formulário de nova senha)
        require __DIR__ . '/../Views/password/reset.php';
    }

    // ============================================================
    // 4. PROCESSAR REDEFINIÇÃO DE SENHA
    // ============================================================
    /**
     * Processa o formulário de redefinição de senha.
     * 
     * Fluxo:
     *   - Valida se as senhas conferem.
     *   - Verifica se o token ainda é válido.
     *   - Gera o hash da nova senha usando bcrypt (password_hash).
     *   - Atualiza a senha na tabela correspondente (admin ou representante).
     *   - Remove o token usado do banco para evitar reuso.
     *   - Exibe mensagem de sucesso com link para login.
     * 
     * @uses $_POST['token']           Token de redefinição
     * @uses $_POST['tipo']            Tipo de usuário (admin ou representante)
     * @uses $_POST['senha']           Nova senha
     * @uses $_POST['confirmar_senha'] Confirmação da nova senha
     * 
     * Rota associada: POST /reset-password
     */
    public function resetPassword(): void {
        $token = $_POST['token'] ?? '';
        $tipo = $_POST['tipo'] ?? '';
        $senha = $_POST['senha'] ?? '';
        $confirmar = $_POST['confirmar_senha'] ?? '';

        // Valida se as senhas conferem
        if ($senha !== $confirmar) {
            echo "Senhas não conferem.";
            exit;
        }

        // Busca o token válido
        $stmt = $this->pdo->prepare("SELECT * FROM password_resets WHERE token = :token AND tipo = :tipo AND expira_em > NOW()");
        $stmt->execute([':token' => $token, ':tipo' => $tipo]);
        $reset = $stmt->fetch();

        if (!$reset) {
            echo "Token inválido ou expirado.";
            exit;
        }

        // Obtém o email associado ao token
        $email = $reset['email'];
        $tabela = $tipo === 'admin' ? 'administradores' : 'representantes';
        
        // Gera o hash seguro da nova senha
        $hash = password_hash($senha, PASSWORD_DEFAULT);

        // Atualiza a senha na tabela correta
        $stmt = $this->pdo->prepare("UPDATE $tabela SET senha = :senha WHERE email = :email");
        $stmt->execute([':senha' => $hash, ':email' => $email]);

        // Remove o token usado (evita reuso)
        $stmt = $this->pdo->prepare("DELETE FROM password_resets WHERE token = :token");
        $stmt->execute([':token' => $token]);

        echo "Senha redefinida com sucesso! <a href='/login'>Faça login</a>";
    }
}