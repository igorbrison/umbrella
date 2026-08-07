<?php
/**
 * Arquivo: Views/dashboard/representante.php
 * Função: Dashboard do representante com gráficos e indicadores.
 */

if (!isset($dados) || !is_array($dados)) {
    $dados = [];
}

$titulo = 'Dashboard';
require __DIR__ . '/../partials/dashboard_header.php';
?>

<h1 style="margin-bottom: 8px;">Dashboard</h1>

<div class="dashboard-grid">
    <!-- ==================== LINHA 1: 3 GRÁFICOS DE RECEITA ==================== -->
    <div class="row-top">
        <!-- Comparativo Anual -->
        <div class="card">
            <h3>Comparativo Anual de Receita</h3>
            <form method="GET" action="/dashboard" class="comparativo-controles">
                <label>Comparar anos:</label>
                <select name="ano1">
                    <?php foreach ($dados['anosDisponiveis'] ?? [] as $ano): ?>
                        <option value="<?= $ano ?>" <?= ($ano == ($dados['ano1'] ?? 0)) ? 'selected' : '' ?>><?= $ano ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="ano2">
                    <?php foreach ($dados['anosDisponiveis'] ?? [] as $ano): ?>
                        <option value="<?= $ano ?>" <?= ($ano == ($dados['ano2'] ?? 0)) ? 'selected' : '' ?>><?= $ano ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-primary">Atualizar</button>
            </form>
            <canvas id="chartComparativoAnual"></canvas>
        </div>

        <!-- Receita Mensal -->
        <div class="card">
            <h3>Receita Mensal (R$)</h3>
            <canvas id="chartReceitaMensal"></canvas>
        </div>

        <!-- Comissão Mensal -->
        <div class="card">
            <h3>Comissão Mensal (R$)</h3>
            <canvas id="chartComissao"></canvas>
        </div>
    </div>

    <!-- ==================== LINHA 2: CLIENTES E LICENÇAS (ROSÇA) ==================== -->
    <div class="row-middle">
        <!-- Clientes -->
        <div class="card" id="cardClientes" style="cursor:pointer;">
            <h3>Clientes</h3>
            <canvas id="chartClientes"></canvas>
            <p>Ativos: <?= $dados['clientes_ativos'] ?? 0 ?> | Inativos: <?= $dados['clientes_inativos'] ?? 0 ?></p>
        </div>

        <!-- Licenças -->
        <div class="card" id="cardLicencas" style="cursor:pointer;">
            <h3>Licenças</h3>
            <canvas id="chartLicencas"></canvas>
            <p>Ativas: <?= $dados['licencas_ativas'] ?? 0 ?> | Expiradas: <?= $dados['licencas_expiradas'] ?? 0 ?></p>
        </div>
    </div>

    <!-- ==================== LINHA 3: CLIENTES EM ATRASO E LICENÇAS GERADAS ==================== -->
    <div class="row-bottom">
        <!-- Clientes em Atraso (DESTAQUE) -->
        <div class="card-destaque" id="cardAtraso" style="cursor:pointer;">
            <h3>Clientes em Atraso</h3>
            <div class="big-number"><?= $dados['clientes_em_atraso'] ?? 0 ?></div>
            <p>Clique para ver detalhes</p>
        </div>

        <!-- Licenças Geradas -->
        <div class="card">
            <h3>Licenças Geradas</h3>
            <canvas id="chartLicencasGeradas"></canvas>
        </div>
    </div>
</div>

<!-- Modal para detalhes -->
<div id="modalDetalhes" class="modal-overlay" style="display:none;">
    <div class="modal-content">
        <span class="modal-close" id="modalDetalhesClose">&times;</span>
        <h2 id="modalTitulo"></h2>
        <ul id="modalLista" style="max-height:400px; overflow-y:auto;"></ul>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Dados vindos do PHP
const clientesDetalhes = <?= json_encode($dados['clientes_detalhes'] ?? []) ?>;
const licencasDetalhes = <?= json_encode($dados['licencas_detalhes'] ?? []) ?>;
const atrasoDetalhes = <?= json_encode($dados['atraso_detalhes'] ?? []) ?>;

// Funções para abrir modal
function abrirModal(titulo, lista) {
    document.getElementById('modalTitulo').textContent = titulo;
    const ul = document.getElementById('modalLista');
    ul.innerHTML = '';
    if (lista.length === 0) {
        ul.innerHTML = '<li>Nenhum registro encontrado.</li>';
    } else {
        lista.forEach(item => {
            const li = document.createElement('li');
            li.textContent = item.nome || item.cliente_nome || item;
            ul.appendChild(li);
        });
    }
    document.getElementById('modalDetalhes').style.display = 'flex';
}

document.getElementById('modalDetalhesClose').addEventListener('click', function() {
    document.getElementById('modalDetalhes').style.display = 'none';
});

// Cliques nos cards (SEM SUFIXOS)
document.getElementById('cardClientes').addEventListener('click', function() {
    const todos = clientesDetalhes.map(c => ({nome: c.nome}));
    abrirModal('Detalhes dos Clientes', todos);
});

document.getElementById('cardLicencas').addEventListener('click', function() {
    const todos = licencasDetalhes.map(l => ({nome: l.cliente_nome}));
    abrirModal('Detalhes das Licenças', todos);
});

document.getElementById('cardAtraso').addEventListener('click', function() {
    const todos = atrasoDetalhes.map(a => ({nome: a.cliente_nome}));
    abrirModal('Clientes em Atraso', todos);
});

// ============================================================
// GRÁFICOS
// ============================================================

// Clientes (Rosca)
new Chart(document.getElementById('chartClientes'), {
    type: 'doughnut',
    data: {
        labels: ['Ativos', 'Inativos'],
        datasets: [{
            data: [<?= $dados['clientes_ativos'] ?? 0 ?>, <?= $dados['clientes_inativos'] ?? 0 ?>],
            backgroundColor: ['#16a34a', '#dc2626']
        }]
    },
    options: { 
        responsive: true, 
        maintainAspectRatio: false,
        plugins: { legend: { display: false } } 
    }
});

// Licenças (Rosca)
new Chart(document.getElementById('chartLicencas'), {
    type: 'doughnut',
    data: {
        labels: ['Ativas', 'Expiradas'],
        datasets: [{
            data: [<?= $dados['licencas_ativas'] ?? 0 ?>, <?= $dados['licencas_expiradas'] ?? 0 ?>],
            backgroundColor: ['#2563eb', '#f59e0b']
        }]
    },
    options: { 
        responsive: true, 
        maintainAspectRatio: false,
        plugins: { legend: { display: false } } 
    }
});

// Receita Mensal (Barras)
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
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                max: <?= $dados['maxReceita'] ?? 1000 ?>,
                ticks: { callback: function(value) { return 'R$ ' + value.toFixed(2); } }
            }
        },
        plugins: { legend: { display: false } }
    }
});

// Comissão Mensal (Linha)
new Chart(document.getElementById('chartComissao'), {
    type: 'line',
    data: {
        labels: <?= json_encode($dados['meses'] ?? []) ?>,
        datasets: [{
            label: 'Comissão (R$)',
            data: <?= json_encode($dados['comissaoMensal'] ?? []) ?>,
            borderColor: '#8b5cf6',
            backgroundColor: 'rgba(139,92,246,0.1)',
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                max: <?= $dados['maxComissao'] ?? 1000 ?>,
                ticks: { callback: function(value) { return 'R$ ' + value.toFixed(2); } }
            }
        },
        plugins: { legend: { display: false } }
    }
});

// Licenças Geradas (Barras)
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
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                max: <?= $dados['maxLicencas'] ?? 5 ?>,
                ticks: { stepSize: 1, precision: 0 }
            }
        },
        plugins: { legend: { display: false } }
    }
});

// Comparativo Anual (Linhas)
new Chart(document.getElementById('chartComparativoAnual'), {
    type: 'line',
    data: {
        labels: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
        datasets: [
            {
                label: '<?= $dados['ano1'] ?? 'Anterior' ?>',
                data: <?= json_encode($dados['receitaAnual1'] ?? []) ?>,
                borderColor: '#2563eb',
                backgroundColor: 'transparent',
                tension: 0.3
            },
            {
                label: '<?= $dados['ano2'] ?? 'Atual' ?>',
                data: <?= json_encode($dados['receitaAnual2'] ?? []) ?>,
                borderColor: '#dc2626',
                backgroundColor: 'transparent',
                tension: 0.3
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            tooltip: { mode: 'index', intersect: false },
            legend: { position: 'top', labels: { boxWidth: 12, padding: 8 } }
        },
        scales: {
            y: {
                beginAtZero: true,
                max: <?= $dados['maxComparativo'] ?? 1000 ?>,
                ticks: { callback: function(value) { return 'R$ ' + value.toFixed(2); } }
            }
        }
    }
});
</script>

<?php require __DIR__ . '/../partials/dashboard_footer.php'; ?>