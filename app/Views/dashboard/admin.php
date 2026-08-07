<?php
/**
 * Arquivo: Views/dashboard/admin.php
 * Função: Dashboard do administrador.
 * 
 * Exibe cards com indicadores gerais do sistema:
 *   - Total de representantes cadastrados.
 *   - Total de clientes (licenças ativas).
 *   - Receita mensal estimada.
 *   - Clientes em atraso.
 * 
 * Os dados são fornecidos pelo DashboardController e repassados
 * no array associativo $dados.
 * 
 * Uso dos parciais:
 *   - dashboard_header.php : barra superior, menu lateral e abertura do main-content.
 *   - dashboard_footer.php : fechamento das tags abertas pelo header.
 */

// Inicializa $dados como array vazio se não definido (evita avisos de análise)
if (!isset($dados) || !is_array($dados)) {
    $dados = [];
}

// Título da página
$titulo = 'Dashboard - Admin';

// Inclui o cabeçalho do painel (barra superior, menu lateral, abertura do main-content)
require __DIR__ . '/../partials/dashboard_header.php';
?>

<h1>Dashboard</h1>

<!-- Cards de indicadores -->
<div class="dashboard-cards">
    <!-- Total de Representantes -->
    <div class="card">
        <h3>Total Representantes</h3>
        <p><?= $dados['totalRepresentantes'] ?? 0 ?></p>
    </div>

    <!-- Total de Clientes -->
    <div class="card">
        <h3>Total Clientes</h3>
        <p><?= $dados['totalClientes'] ?? 0 ?></p>
    </div>

    <!-- Receita Mensal -->
    <div class="card">
        <h3>Receita Mensal</h3>
        <p>R$ <?= number_format($dados['receitaMensal'] ?? 0, 2, ',', '.') ?></p>
    </div>

    <!-- Clientes em Atraso -->
    <div class="card">
        <h3>Clientes em Atraso</h3>
        <p><?= $dados['clientesEmAtraso'] ?? 0 ?></p>
    </div>

    <!-- Placeholders para os demais cards (serão preenchidos posteriormente) -->
</div>

<?php
// Inclui o rodapé do painel (fecha main-content, div wrapper, body e html)
require __DIR__ . '/../partials/dashboard_footer.php';
?>