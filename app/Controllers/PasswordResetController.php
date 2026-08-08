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
 *   2. Sistema gera token, salva em password_resets e envia link por e‑mail.
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
     * Obtém a instância única do banco de dados e força o fuso UTC na sessão MySQL,
     * garantindo que todas as funções de data trabalhem no mesmo fuso que o PHP (UTC).
     */
    public function __construct() {
        $this->pdo = Database::getInstance();
        // Força a sessão MySQL a usar UTC, alinhando com gmdate() do PHP
        $this->pdo->exec("SET time_zone = '+00:00'");
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
     *   - Gera um token único (64 caracteres hexadecimais) com validade de 1 hora (UTC).
     *   - Remove tokens antigos do mesmo email/tipo.
     *   - Insere o novo token na tabela password_resets.
     *   - Envia o link de redefinição por email usando o helper Email (PHPMailer).
     *   - Retorna JSON para requisições AJAX; fallback HTML caso contrário.
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
            $this->responder(false, 'Email é obrigatório.');
            return;
        }

        // Verifica se o email existe na tabela correta
        $tabela = $tipo === 'admin' ? 'administradores' : 'representantes';
        $stmt = $this->pdo->prepare("SELECT id, email FROM $tabela WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch();

        if (!$usuario) {
            // Mensagem genérica: não revela se o email existe (segurança)
            $this->responder(true, 'Se o email estiver cadastrado, um link de recuperação foi enviado.');
            return;
        }

        // Gera token único e define expiração (1 hora) – gmdate() já está em UTC
        $token = bin2hex(random_bytes(32));
        $expira = gmdate('Y-m-d H:i:s', strtotime('+1 hour'));

        // Remove tokens antigos do mesmo email/tipo (evita acúmulo)
        $stmt = $this->pdo->prepare("DELETE FROM password_resets WHERE email = :email AND tipo = :tipo");
        $stmt->execute([':email' => $email, ':tipo' => $tipo]);

        // Insere o novo token no banco (a conexão MySQL já está em UTC)
        $stmt = $this->pdo->prepare("INSERT INTO password_resets (email, token, tipo, expira_em) VALUES (:email, :token, :tipo, :expira)");
        $stmt->execute([':email' => $email, ':token' => $token, ':tipo' => $tipo, ':expira' => $expira]);

        // Prepara o e‑mail de redefinição
        $assunto = "Redefinição de senha - Umbrella Corporation";
        $link = "http://umbrella.test/reset-password?token=$token&tipo=$tipo";
        $mensagem = $this->montarEmailRedefinicao($link);

        // Tenta enviar o e‑mail utilizando o helper Email (PHPMailer)
        $enviado = Email::enviar($email, $assunto, $mensagem);

        if ($enviado) {
            $this->responder(true, 'Email enviado com sucesso! Verifique sua caixa de entrada.');
        } else {
            $this->responder(false, 'Não foi possível enviar o e‑mail. Tente novamente mais tarde.');
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
        
        if (empty($token) || empty($tipo)) {
            echo "Token inválido.";
            exit;
        }

        // A conexão já está em UTC, então NOW() retorna UTC
        $stmt = $this->pdo->prepare("SELECT * FROM password_resets WHERE token = :token AND tipo = :tipo AND expira_em > NOW()");
        $stmt->execute([':token' => $token, ':tipo' => $tipo]);
        $reset = $stmt->fetch();

        if (!$reset) {
            echo "Token inválido ou expirado.";
            exit;
        }

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

        if ($senha !== $confirmar) {
            echo "Senhas não conferem.";
            exit;
        }

        // A conexão está em UTC, NOW() retorna UTC
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

        $stmt = $this->pdo->prepare("UPDATE $tabela SET senha = :senha WHERE email = :email");
        $stmt->execute([':senha' => $hash, ':email' => $email]);

        $stmt = $this->pdo->prepare("DELETE FROM password_resets WHERE token = :token");
        $stmt->execute([':token' => $token]);

        echo "Senha redefinida com sucesso! <a href='/login'>Faça login</a>";
    }

    // ============================================================
    // MÉTODOS AUXILIARES PRIVADOS
    // ============================================================

    /**
     * Padroniza a resposta do controlador.
     */
    private function responder(bool $sucesso, string $mensagem): void {
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
              && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    if ($isAjax) {
        echo json_encode(['sucesso' => $sucesso, 'mensagem' => $mensagem]);
        exit;
    }

    // Fallback HTML (agora com classes)
    $classe = $sucesso ? 'feedback-sucesso' : 'feedback-erro';
    echo "<div class=\"$classe\">$mensagem</div>";
    if (!$sucesso) {
        echo "<a href='/forgot-password'>Voltar</a>";
    }
    exit;
}

    /**
     * Monta o corpo do e‑mail de redefinição em HTML compatível com clientes de e‑mail.
     */
   /**
 * Monta o corpo do e‑mail de redefinição de senha em HTML.
 * 
 * Responsável por gerar um template de e‑mail profissional e compatível
 * com a maioria dos clientes de e‑mail (Gmail, Outlook, etc.).
 * 
 * O layout é baseado em tabelas, garantindo melhor compatibilidade.
 * O cabeçalho exibe o nome da empresa como texto estilizado,
 * evitando problemas de carregamento de imagens externas.
 * 
 * @param string $link URL completa do link de redefinição de senha.
 * @return string HTML pronto para ser enviado como corpo do e‑mail.
 */
private function montarEmailRedefinicao(string $link): string {
    return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0; padding:0; background:#f0f4f8; font-family:Arial, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width:500px; margin:30px auto; background:#fff; border-radius:8px; overflow:hidden; border:1px solid #e0e5ec;">
        <tr>
            <td style="background:#1a2a3a; padding:20px; text-align:center;">
                <span style="color:#fff; font-size:22px; font-weight:bold; letter-spacing:0.5px;">UMBRELLA CORPORATION</span>
            </td>
        </tr>
        <tr>
            <td style="padding:30px;">
                <h2 style="color:#1a2a3a; margin-top:0;">Redefinição de senha</h2>
                <p style="color:#333;">Você solicitou a redefinição de sua senha. Clique no botão abaixo para criar uma nova senha:</p>
                <table cellpadding="0" cellspacing="0" style="margin:20px 0;">
                    <tr>
                        <td align="center" bgcolor="#2563eb" style="border-radius:6px;">
                            <a href="$link" target="_blank" style="display:inline-block; padding:12px 30px; color:#fff; text-decoration:none; font-weight:bold;">Redefinir senha</a>
                        </td>
                    </tr>
                </table>
                <p style="font-size:13px; color:#6c7a8a;">Se o botão não funcionar, copie e cole o link abaixo no seu navegador:</p>
                <p style="font-size:12px; color:#6c7a8a; word-break:break-all;"><a href="$link" style="color:#6c7a8a;">$link</a></p>
                <p style="font-size:13px; color:#6c7a8a;">Este link é válido por 1 hora. Se você não solicitou esta alteração, ignore este e‑mail.</p>
            </td>
        </tr>
        <tr>
            <td style="background:#f8fafc; padding:15px; text-align:center; font-size:12px; color:#9aa6b5;">
                © 2026 Umbrella Corporation
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
}
}