<?php
/**
 * Arquivo: Views/dashboard/representante.php
 * Função: Dashboard do representante.
 * 
 * Exibe gráficos e indicadores de desempenho.
 * 
 * Uso dos parciais:
 *   - dashboard_header.php : barra superior, menu lateral e abertura do main-content.
 *   - dashboard_footer.php : fechamento das tags abertas pelo header.
 */

// Inicializa $dados como array vazio se não definido (evita avisos de análise)
if (!isset($dados) || !is_array($dados)) {
    $dados = [];
}

$titulo = 'Dashboard';
require __DIR__ . '/../partials/dashboard_header.php';
?>

<h1>Dashboard</h1>

<div class="dashboard-grid">
    <!-- Clientes Ativos/Inativos -->
    <div class="card">
        <h3>Clientes</h3>
        <canvas id="chartClientes"></canvas>
        <p>Ativos: <?= $dados['clientes_ativos'] ?? 0 ?> | Inativos: <?= $dados['clientes_inativos'] ?? 0 ?></p>
    </div>

    <!-- Licenças -->
    <div class="card">
        <h3>Licenças</h3>
        <canvas id="chartLicencas"></canvas>
        <p>Ativas: <?= $dados['licencas_ativas'] ?? 0 ?> | Expiradas: <?= $dados['licencas_expiradas'] ?? 0 ?></p>
    </div>

    <!-- Receita Mensal (Bar) -->
    <div class="card wide">
        <h3>Receita Mensal (R$)</h3>
        <canvas id="chartReceitaMensal"></canvas>
    </div>

    <!-- Comissão do Representante (Line) -->
    <div class="card">
        <h3>Comissão Mensal (R$)</h3>
        <canvas id="chartComissao"></canvas>
    </div>

    <!-- Licenças Geradas por Mês (Bar) -->
    <div class="card">
        <h3>Licenças Geradas</h3>
        <canvas id="chartLicencasGeradas"></canvas>
    </div>

    <!-- Comparativo Anual (Multilinha) -->
    <div class="card wide">
        <h3>Comparativo Anual de Receita</h3>
        <canvas id="chartComparativoAnual"></canvas>
    </div>

    <!-- Card Numérico: Clientes em Atraso -->
    <div class="card">
        <h3>Clientes em Atraso</h3>
        <div class="big-number"><?= $dados['clientes_em_atraso'] ?? 0 ?></div>
        <p>licenças expiradas/inativas</p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Clientes Ativos/Inativos (Doughnut)
    new Chart(document.getElementById('chartClientes'), {
        type: 'doughnut',
        data: {
            labels: ['Ativos', 'Inativos'],
            datasets: [{
                data: [<?= $dados['clientes_ativos'] ?? 0 ?>, <?= $dados['clientes_inativos'] ?? 0 ?>],
                backgroundColor: ['#16a34a', '#dc2626']
            }]
        },
        options: { responsive: true }
    });

    // Licenças Ativas/Expiradas (Doughnut)
    new Chart(document.getElementById('chartLicencas'), {
        type: 'doughnut',
        data: {
            labels: ['Ativas', 'Expiradas'],
            datasets: [{
                data: [<?= $dados['licencas_ativas'] ?? 0 ?>, <?= $dados['licencas_expiradas'] ?? 0 ?>],
                backgroundColor: ['#2563eb', '#f59e0b']
            }]
        },
        options: { responsive: true }
    });

    // Receita Mensal (Bar)
    new Chart(document.getElementById('chartReceitaMensal'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($dados['meses'] ?? []) ?>,
            datasets: [{
                label: 'Receita (R$)',
                data: <?= json_encode($dados['receitaMensal'] ?? []) ?>,
                backgroundColor: '#2563eb'
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    // Comissão Mensal (Line)
    new Chart(document.getElementById('chartComissao'), {
        type: 'line',
        data: {
            labels: <?= json_encode($dados['meses'] ?? []) ?>,
            datasets: [{
                label: 'Comissão (R$)',
                data: <?= json_encode($dados['comissaoMensal'] ?? []) ?>,
                borderColor: '#8b5cf6',
                backgroundColor: 'rgba(139,92,246,0.1)',
                fill: true
            }]
        },
        options: { responsive: true }
    });

    // Licenças Geradas por Mês (Bar)
    new Chart(document.getElementById('chartLicencasGeradas'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($dados['meses'] ?? []) ?>,
            datasets: [{
                label: 'Licenças',
                data: <?= json_encode($dados['licencasGeradas'] ?? []) ?>,
                backgroundColor: '#059669'
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true, precision: 0 } } }
    });

    // Comparativo Anual (Multilinha)
    new Chart(document.getElementById('chartComparativoAnual'), {
        type: 'line',
        data: {
            labels: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
            datasets: [
                {
                    label: '<?= $dados['comparativoAnos'][0] ?? 'Anterior' ?>',
                    data: <?= json_encode($dados['receitaAnual1'] ?? []) ?>,
                    borderColor: '#2563eb',
                    backgroundColor: 'transparent',
                    tension: 0.3
                },
                {
                    label: '<?= $dados['comparativoAnos'][1] ?? 'Atual' ?>',
                    data: <?= json_encode($dados['receitaAnual2'] ?? []) ?>,
                    borderColor: '#dc2626',
                    backgroundColor: 'transparent',
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: { mode: 'index', intersect: false }
            }
        }
    });
</script>

<style>
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    .card {
        background: #fff;
        border: 1px solid #e0e5ec;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .card.wide {
        grid-column: span 2;
    }
    .card h3 {
        margin-top: 0;
        margin-bottom: 16px;
        color: #1a2a3a;
    }
    .big-number {
        font-size: 48px;
        font-weight: 700;
        color: #dc2626;
    }
</style>

<?php require __DIR__ . '/../partials/dashboard_footer.php'; ?>