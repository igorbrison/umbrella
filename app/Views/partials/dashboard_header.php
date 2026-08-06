<?php
/**
 * Arquivo: Views/partials/dashboard_header.php
 * Função: Cabeçalho do painel interno (dashboard) para páginas restritas.
 * 
 * Responsável por:
 *   - Verificar a autenticação do usuário (admin ou representante).
 *   - Definir o nome de exibição, URL de logout e perfil ativo.
 *   - Gerar a estrutura HTML inicial do painel:
 *       • Barra superior (topbar) com logo, nome do usuário e botão sair.
 *       • Menu lateral (sidebar) com links específicos para cada perfil.
 *       • Abertura da área de conteúdo principal (main-content).
 * 
 * Deve ser utilizado em conjunto com o arquivo dashboard_footer.php,
 * que fecha as tags abertas por este cabeçalho.
 * 
 * Uso: Incluir no início de cada view interna do painel, após definir $titulo.
 * Exemplo:
 *   $titulo = 'Minha Página';
 *   require __DIR__ . '/../partials/dashboard_header.php';
 *   // conteúdo da página...
 *   require __DIR__ . '/../partials/dashboard_footer.php';
 * 
 * Espera que a sessão esteja iniciada e contenha:
 *   - $_SESSION['admin_nome'] ou $_SESSION['representante_nome'] (para exibição).
 *   - $_SESSION['admin_id'] ou $_SESSION['representante_id'] (para perfil).
 */

// ============================================================
// 1. DETERMINA O PERFIL DO USUÁRIO LOGADO
// ============================================================
$perfil = null;
if (isset($_SESSION['admin_id'])) {
    // Administrador autenticado
    $perfil = 'admin';
    $nomeUsuario = $_SESSION['admin_nome'] ?? 'Administrador';
    $logoutUrl = '/admin/logout';
} elseif (isset($_SESSION['representante_id'])) {
    // Representante autenticado
    $perfil = 'representante';
    $nomeUsuario = $_SESSION['representante_nome'] ?? 'Representante';
    $logoutUrl = '/logout';
} else {
    // Nenhum usuário autenticado: redireciona para a tela de login
    header('Location: /login');
    exit;
}

// ============================================================
// 2. DEFINE O TÍTULO DA PÁGINA (FALLBACK)
// ============================================================
// Se a view não definiu $titulo, usa 'Dashboard' como valor padrão
$titulo = $titulo ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?> - Umbrella Corporation</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="icon" href="/img/logo-sem-fundo.png" type="image/x-icon">
    <!-- Font Awesome (biblioteca de ícones) -->
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
            <!-- Nome do usuário logado -->
            <span class="topbar-user"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($nomeUsuario) ?></span>
            <!-- Link de logout (URL varia conforme o perfil) -->
            <a href="<?= $logoutUrl ?>" class="topbar-logout"><i class="fas fa-sign-out-alt"></i> Sair</a>
        </div>
    </header>

    <!-- ==================== MENU LATERAL + CONTEÚDO PRINCIPAL ==================== -->
    <div class="dashboard-wrapper">
        <!-- Menu lateral (sidebar) -->
        <nav class="sidebar">
            <ul class="sidebar-menu">
                <!-- Link para o Dashboard (comum a ambos os perfis) -->
                <li><a href="/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>

                <?php if ($perfil === 'admin'): ?>
                    <!-- ===== LINKS DO ADMINISTRADOR ===== -->
                    <li class="menu-divider">Administração</li>
                    <li><a href="/admin/representantes"><i class="fas fa-users"></i> Representantes</a></li>
                    <li><a href="/admin/modulos"><i class="fas fa-cubes"></i> Módulos</a></li>
                    <li><a href="/admin/licencas"><i class="fas fa-key"></i> Licenças</a></li>
                    <li><a href="/admin/clientes"><i class="fas fa-building"></i> Clientes (Admin)</a></li>
                    <li><a href="/admin/configuracao"><i class="fas fa-cog"></i> Configurações</a></li>
                <?php else: ?>
                    <!-- ===== LINKS DO REPRESENTANTE ===== -->
                    <li class="menu-divider">Meu Painel</li>
                    <li><a href="/painel/clientes"><i class="fas fa-user-tie"></i> Meus Clientes</a></li>
                    <li><a href="/painel/licencas"><i class="fas fa-key"></i> Licenças</a></li>
                    <li><a href="/painel/solicitacoes"><i class="fas fa-ticket-alt"></i> Solicitações</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <!-- Área de conteúdo principal (abre a div que será fechada no dashboard_footer) -->
        <main class="main-content">