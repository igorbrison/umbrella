<?php
/**
 * Arquivo: Views/dashboard/representante.php
 * Função: Dashboard do representante.
 * 
 * Exibe cards com indicadores do seu negócio:
 *   - Total de clientes cadastrados.
 *   - Licenças a vencer nos próximos 7 dias.
 *   - Receita mensal estimada.
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
$titulo = 'Dashboard';

// Inclui o cabeçalho do painel (barra superior, menu lateral, abertura do main-content)
require __DIR__ . '/../partials/dashboard_header.php';
?>

<h1>Dashboard</h1>

<!-- Cards de indicadores -->
<div class="dashboard-cards">
    <!-- Total de Clientes do Representante -->
    <div class="card">
        <h3>Meus Clientes</h3>
        <p><?= $dados['totalClientes'] ?? 0 ?></p>
    </div>

    <!-- Licenças Próximas do Vencimento (7 dias) -->
    <div class="card">
        <h3>Licenças a Vencer (7 dias)</h3>
        <p><?= $dados['proximasVencer'] ?? 0 ?></p>
    </div>

    <!-- Receita Mensal Estimada -->
    <div class="card">
        <h3>Receita Mensal</h3>
        <p>R$ <?= number_format($dados['receitaMensal'] ?? 0, 2, ',', '.') ?></p>
    </div>

    <!-- Placeholders para os demais cards (serão preenchidos posteriormente) -->
</div>

<?php
// Inclui o rodapé do painel (fecha main-content, div wrapper, body e html)
require __DIR__ . '/../partials/dashboard_footer.php';
?>