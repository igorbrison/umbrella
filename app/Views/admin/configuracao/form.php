<?php
/**
 * Arquivo: Views/admin/configuracao/form.php
 * Função: VIEW de configuração completa do sistema (painel admin).
 * 
 * Permite ao administrador atualizar:
 *   - Nome da empresa e e-mail de contato.
 *   - Salário mínimo (base de cálculo dos módulos).
 *   - Dados do servidor SMTP para envio de e-mails.
 *   - Dias de antecedência para cobrança automática.
 */

// Valores default caso não venham do controller
$configs = $configs ?? [];
$salarioMinimo = $configs['salario_minimo'] ?? 1621.00;
$nomeEmpresa   = $configs['nome_empresa'] ?? 'Umbrella Corporation';
$emailContato  = $configs['email_contato'] ?? '';
$smtpHost      = $configs['smtp_host'] ?? '';
$smtpPort      = $configs['smtp_port'] ?? 587;
$smtpUser      = $configs['smtp_user'] ?? '';
$smtpPass      = $configs['smtp_pass'] ?? '';
$diasAntecedencia = $configs['dias_antecedencia_cobranca'] ?? 3;

$titulo = 'Configurações do Sistema';
require __DIR__ . '/../../partials/dashboard_header.php';
?>

<h1>Configurações</h1>

<form method="POST" action="/admin/configuracao/salvar">
    <!-- ==================== IDENTIDADE DA EMPRESA ==================== -->
    <fieldset>
        <legend>Identidade da Empresa</legend>
        <div class="form-row">
            <div class="form-col">
                <label>Nome da Empresa:
                    <input type="text" name="nome_empresa" value="<?= htmlspecialchars($nomeEmpresa) ?>">
                </label>
            </div>
            <div class="form-col">
                <label>E-mail de Contato:
                    <input type="email" name="email_contato" value="<?= htmlspecialchars($emailContato) ?>">
                </label>
            </div>
        </div>
    </fieldset>

    <!-- ==================== SALÁRIO MÍNIMO ==================== -->
    <fieldset>
        <legend>Salário Mínimo</legend>
        <div class="form-row">
            <div class="form-col">
                <label>Valor (R$):
                    <input type="number" step="0.01" name="salario_minimo" value="<?= $salarioMinimo ?>">
                </label>
            </div>
        </div>
    </fieldset>

    <!-- ==================== CONFIGURAÇÃO DE E-MAIL (SMTP) ==================== -->
    <fieldset>
        <legend>Configuração de E-mail (SMTP)</legend>
        <div class="form-row">
            <div class="form-col">
                <label>Servidor SMTP:
                    <input type="text" name="smtp_host" value="<?= htmlspecialchars($smtpHost) ?>">
                </label>
            </div>
            <div class="form-col">
                <label>Porta:
                    <input type="number" name="smtp_port" value="<?= $smtpPort ?>">
                </label>
            </div>
        </div>
        <div class="form-row">
            <div class="form-col">
                <label>Usuário:
                    <input type="text" name="smtp_user" value="<?= htmlspecialchars($smtpUser) ?>">
                </label>
            </div>
            <div class="form-col">
                <label>Senha:
                    <input type="password" name="smtp_pass" value="<?= htmlspecialchars($smtpPass) ?>">
                </label>
            </div>
        </div>
    </fieldset>

    <!-- ==================== COBRANÇA AUTOMÁTICA ==================== -->
    <fieldset>
        <legend>Cobrança Automática</legend>
        <div class="form-row">
            <div class="form-col">
                <label>Dias de antecedência para cobrança:
                    <input type="number" name="dias_antecedencia_cobranca" value="<?= $diasAntecedencia ?>" min="1" max="10">
                </label>
                <span class="info">Quantos dias antes do vencimento (dia 5) o e-mail será enviado. Padrão: 3.</span>
            </div>
        </div>
    </fieldset>

    <!-- ==================== BOTÕES ==================== -->
    <div class="form-actions">
        <button type="submit" class="btn-primary">Salvar Configurações</button>
    </div>
</form>

<?php require __DIR__ . '/../../partials/dashboard_footer.php'; ?>