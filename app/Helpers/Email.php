<?php
/**
 * Arquivo: Helpers/Email.php
 * Função: Helper para envio de e-mails através do PHPMailer.
 * 
 * Responsável por:
 *   - Configurar a conexão SMTP com base nas credenciais definidas em config/email.php.
 *   - Enviar e-mails de forma segura utilizando TLS e autenticação.
 *   - Retornar true/false para que o controlador possa tratar o resultado do envio.
 * 
 * Utiliza a biblioteca PHPMailer (instalada via Composer).
 * 
 * Uso:
 *   require_once __DIR__ . '/../Helpers/Email.php';
 *   Email::enviar('destino@email.com', 'Assunto', 'Corpo da mensagem');
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/email.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Email {
    
    /**
     * Envia um e-mail utilizando SMTP autenticado.
     * 
     * @param string $destinatario Endereço de e-mail do destinatário.
     * @param string $assunto Assunto da mensagem.
     * @param string $mensagem Corpo do e-mail (texto puro, sem HTML).
     * @return bool True se o envio for bem-sucedido, false caso contrário.
     */
    public static function enviar(string $destinatario, string $assunto, string $mensagem): bool {
        // Cria uma nova instância do PHPMailer com exceções habilitadas
        $mail = new PHPMailer(true);
        try {
            // ============================================================
            // 1. CONFIGURAÇÃO DO SERVIDOR SMTP
            // ============================================================
            // Utiliza as constantes definidas em config/email.php
            $mail->isSMTP();                                      // Define o envio via SMTP
            $mail->Host       = MAIL_HOST;                        // Endereço do servidor SMTP
            $mail->SMTPAuth   = true;                             // Habilita autenticação
            $mail->Username   = MAIL_USERNAME;                    // Usuário (e-mail) de autenticação
            $mail->Password   = MAIL_PASSWORD;                    // Senha ou senha de app
            $mail->SMTPSecure = MAIL_ENCRYPTION;                  // Tipo de criptografia (tls/ssl)
            $mail->Port       = MAIL_PORT;                        // Porta do servidor SMTP

            // ============================================================
            // 2. REMETENTE E DESTINATÁRIO
            // ============================================================
            $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);    // Quem está enviando
            $mail->addAddress($destinatario);                     // Para quem vai o e-mail

            // ============================================================
            // 3. CONTEÚDO DA MENSAGEM
            // ============================================================
            $mail->isHTML(false);                                 // Envia como texto puro (não HTML)
            $mail->Subject = $assunto;                            // Assunto do e-mail
            $mail->Body    = $mensagem;                           // Corpo da mensagem

            // ============================================================
            // 4. ENVIO
            // ============================================================
            $mail->send();
            return true;                                          // Envio bem-sucedido
        } catch (Exception $e) {
            // Registra o erro no log do servidor para depuração
            error_log("Erro ao enviar e-mail: {$mail->ErrorInfo}");
            return false;                                         // Falha no envio
        }
    }
}