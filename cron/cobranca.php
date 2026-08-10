<?php
/**
 * Script de cobrança automática.
 * 
 * Aceita ?forcar=1 para ignorar a data e enviar a todos os pendentes.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Models/Database.php';
require_once __DIR__ . '/../app/Models/Cobranca.php';
require_once __DIR__ . '/../app/Models/Configuracao.php';
require_once __DIR__ . '/../app/Helpers/Email.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$forcar = isset($_GET['forcar']) && $_GET['forcar'] === '1';

$configModel = new Configuracao();
$diasAntecedencia = (int)($configModel->get('dias_antecedencia_cobranca') ?? 3);

$cobrancaModel = new Cobranca();
$clientes = $cobrancaModel->clientesParaCobrarHoje($diasAntecedencia, $forcar);

if (empty($clientes)) {
    echo "Nenhum cliente para cobrar hoje.\n";
    exit;
}

$smtpHost = $configModel->get('smtp_host');
$smtpPort = $configModel->get('smtp_port') ?: 587;
$smtpUser = $configModel->get('smtp_user');
$smtpPass = $configModel->get('smtp_pass');
$emailContato = $configModel->get('email_contato') ?: $smtpUser;   // fallback

if (empty($emailContato)) {
    echo "Erro: e-mail do remetente não configurado.\n";
    exit;
}

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = $smtpHost;
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtpUser;
    $mail->Password   = $smtpPass;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $smtpPort;
    $mail->CharSet    = 'UTF-8';
    $mail->setFrom($emailContato, $configModel->get('nome_empresa') ?: 'Umbrella Corporation');

    $hoje = new DateTime();
    $mesReferencia = $hoje->format('Y-m');

    foreach ($clientes as $cliente) {
        try {
            $mail->clearAddresses();
            $mail->addAddress($cliente['email'], $cliente['nome']);

            if (!empty($cliente['representante_email'])) {
                $mail->addCC($cliente['representante_email'], $cliente['representante_nome']);
            }

            $mail->Subject = 'Aviso de vencimento da sua licença';
            $mail->Body    = "Olá {$cliente['nome']},\n\n"
                           . "Sua licença venceu ou vencerá em breve ({$cliente['data_expiracao']}).\n"
                           . "Valor total: R$ " . number_format($cliente['valor_total'], 2, ',', '.') . "\n\n"
                           . "Por favor, regularize o pagamento para evitar interrupção do serviço.\n\n"
                           . "Atenciosamente,\n" . ($configModel->get('nome_empresa') ?: 'Umbrella Corporation');

            $mail->send();
            $cobrancaModel->registrarLog($cliente['id'], $mesReferencia, true, 'E-mail enviado com sucesso.');
            echo "Cobrança enviada para {$cliente['email']} (Cliente: {$cliente['nome']})\n";
        } catch (Exception $e) {
            $cobrancaModel->registrarLog($cliente['id'], $mesReferencia, false, $mail->ErrorInfo);
            echo "Erro ao enviar para {$cliente['email']}: {$mail->ErrorInfo}\n";
        }
    }
} catch (Exception $e) {
    echo "Erro na configuração do e-mail: {$mail->ErrorInfo}\n";
}