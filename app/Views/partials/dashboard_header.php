<?php
/**
 * Arquivo: Views/partials/dashboard_header.php
 * Função: Cabeçalho do painel interno (dashboard) para páginas restritas.
 * 
 * Responsável por:
 *   - Verificar a autenticação do usuário (admin ou representante).
 *   - Definir o nome de exibição, URL de logout e perfil ativo.
 *   - Gerar a estrutura HTML inicial do painel:
 *       • Barra superior (topbar) com logo, nome do usuário (clicável) e botão sair.
 *       • Dropdown com dados do perfil e opção de alterar senha.
 *       • Modal para alteração de senha (com campos e validação).
 *       • Menu lateral (sidebar) com links específicos para cada perfil.
 *       • Abertura da área de conteúdo principal (main-content).
 * 
 * Deve ser utilizado em conjunto com o arquivo dashboard_footer.php,
 * que fecha as tags abertas por este cabeçalho.
 */

require_once __DIR__ . '/../../Models/Representante.php';
require_once __DIR__ . '/../../Models/Admin.php';

// ============================================================
// 2. DETERMINA O PERFIL DO USUÁRIO LOGADO E CARREGA SEUS DADOS
// ============================================================
$perfil = null;
$dadosUsuario = [];
$ultimaAlteracaoSenha = 'Nunca';

if (isset($_SESSION['admin_id'])) {
    $perfil = 'admin';
    $nomeUsuario = $_SESSION['admin_nome'] ?? 'Administrador';
    $logoutUrl = '/admin/logout';
    $emailUsuario = $_SESSION['admin_email'] ?? '';

    if (!empty($emailUsuario)) {
        $adminModel = new Admin();
        $adminData = $adminModel->buscarPorEmail($emailUsuario);
        if ($adminData) {
            $dadosUsuario = $adminData;
            $ultimaAlteracaoSenha = $adminData['atualizado_em'] ?? '';
        }
    }
} elseif (isset($_SESSION['representante_id'])) {
    $perfil = 'representante';
    $nomeUsuario = $_SESSION['representante_nome'] ?? 'Representante';
    $logoutUrl = '/logout';
    $emailUsuario = $_SESSION['representante_email'] ?? '';

    if (!empty($emailUsuario)) {
        $repModel = new Representante();
        $repData = $repModel->buscarPorEmail($emailUsuario);
        if ($repData) {
            $dadosUsuario = $repData;
            $ultimaAlteracaoSenha = $repData['atualizado_em'] ?? '';
        }
    }
} else {
    header('Location: /login');
    exit;
}

$titulo = $titulo ?? 'Dashboard';

function formatarDataHora(string $data): string {
    if (empty($data)) return 'Nunca';
    try {
        $dt = new DateTime($data);
        return $dt->format('d/m/Y \à\s H:i');
    } catch (\Exception $e) {
        return 'Nunca';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?> - Umbrella Corporation</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="icon" href="/img/logo-sem-fundo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="dashboard">

    <!-- ==================== BARRA SUPERIOR (TOPBAR) ==================== -->
    <header class="topbar">
        <div class="topbar-left">
            <img src="/img/logo-sem-fundo.png" alt="Logo" class="topbar-logo">
            <span class="topbar-title">Umbrella Corporation</span>
        </div>
        <div class="topbar-right">
            <div class="topbar-dropdown">
                <span class="topbar-user" id="userDropdownToggle">
                    <i class="fas fa-user-circle"></i> <?= htmlspecialchars($nomeUsuario) ?>
                    <i class="fas fa-chevron-down" style="font-size:12px; margin-left:6px;"></i>
                </span>
                <div class="dropdown-menu" id="userDropdownMenu">
                    <div class="dropdown-header">
                        <strong><?= htmlspecialchars($nomeUsuario) ?></strong>
                        <small><?= htmlspecialchars($emailUsuario) ?></small>
                    </div>
                    <div class="dropdown-divider"></div>

                    <?php if ($perfil === 'representante' && !empty($dadosUsuario)): ?>
                        <div class="dropdown-item">
                            <i class="fas fa-building"></i> CNPJ: <?= htmlspecialchars($dadosUsuario['cnpj'] ?? '') ?>
                        </div>
                        <div class="dropdown-item">
                            <i class="fas fa-tag"></i> <?= htmlspecialchars($dadosUsuario['nome_fantasia'] ?? '') ?>
                        </div>
                        <div class="dropdown-item">
                            <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($dadosUsuario['municipio'] ?? '') ?>/<?= htmlspecialchars($dadosUsuario['estado'] ?? '') ?>
                        </div>
                    <?php elseif ($perfil === 'admin' && !empty($dadosUsuario)): ?>
                        <div class="dropdown-item">
                            <i class="fas fa-envelope"></i> <?= htmlspecialchars($dadosUsuario['email'] ?? '') ?>
                        </div>
                        <div class="dropdown-item">
                            <i class="fas fa-user-shield"></i> Administrador
                        </div>
                    <?php endif; ?>

                    <div class="dropdown-divider"></div>

                    <a href="/<?= $perfil === 'admin' ? 'admin' : 'painel' ?>/perfil" class="dropdown-item">
                        <i class="fas fa-edit"></i> Editar Perfil
                    </a>
                    <a href="#" class="dropdown-item" id="btnAlterarSenha">
                        <i class="fas fa-key"></i> Alterar Senha
                    </a>

                  <?php if ($perfil !== 'admin'): ?>
                <div class="dropdown-divider"></div>

                <div class="dropdown-item" style="font-size:12px; color:#999; cursor:default;">
                    <i class="fas fa-clock"></i> Última alteração: <?= formatarDataHora($ultimaAlteracaoSenha) ?>
                </div>
                <?php endif; ?>
                </div>
            </div>

            <a href="<?= $logoutUrl ?>" class="topbar-logout"><i class="fas fa-sign-out-alt"></i> Sair</a>
        </div>
    </header>

    <!-- ==================== MODAL ALTERAR SENHA ==================== -->
    <div id="modalSenha" class="modal-overlay" style="display:none;">
        <div class="modal-content">
            <span class="modal-close" id="modalSenhaClose">&times;</span>
            <h2>Alterar Senha</h2>
            <p>Preencha os campos abaixo para atualizar sua senha.</p>

            <form method="POST" action="/<?= $perfil === 'admin' ? 'admin' : 'painel' ?>/alterar-senha" id="formAlterarSenha">
                <div class="input-group">
                    <label for="senha_atual">Senha Atual</label>
                    <input type="password" id="senha_atual" name="senha_atual" required placeholder="Digite sua senha atual">
                </div>
                <div class="input-group">
                    <label for="nova_senha">Nova Senha</label>
                    <input type="password" id="nova_senha" name="nova_senha" required placeholder="Digite a nova senha" minlength="6">
                </div>
                <div class="input-group">
                    <label for="confirmar_senha">Confirmar Nova Senha</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" required placeholder="Confirme a nova senha">
                </div>
                <button type="submit" class="btn-entrar">Salvar Nova Senha</button>
            </form>
            <div id="msgSenha" style="margin-top:10px; display:none;"></div>
        </div>
    </div>

    <!-- ==================== MENU LATERAL + CONTEÚDO PRINCIPAL ==================== -->
    <div class="dashboard-wrapper">
        <nav class="sidebar">
            <ul class="sidebar-menu">
                <li><a href="/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>

                <?php if ($perfil === 'admin'): ?>
                    <li class="menu-divider">Administração</li>
                    <li><a href="/admin/representantes"><i class="fas fa-users"></i> Representantes</a></li>
                    <li><a href="/admin/modulos"><i class="fas fa-cubes"></i> Módulos</a></li>
                    <li><a href="/admin/clientes"><i class="fas fa-building"></i> Clientes</a></li>
                    <li><a href="/admin/solicitacoes"><i class="fas fa-ticket-alt"></i> Solicitações</a></li>
                    <li><a href="/admin/configuracao"><i class="fas fa-cog"></i> Configurações</a></li>
                <?php else: ?>
                    <li class="menu-divider">Meu Painel</li>
                    <li><a href="/painel/clientes"><i class="fas fa-user-tie"></i> Meus Clientes</a></li>
                    <li><a href="/painel/solicitacoes"><i class="fas fa-ticket-alt"></i> Solicitações</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <main class="main-content">