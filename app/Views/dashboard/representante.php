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

<?php if (($dados['clientes_em_atraso'] ?? 0) > 0): ?>
<div class="alerta-banner" style="display:flex; align-items:center; gap:10px; margin-bottom:16px; cursor:pointer;" onclick="window.location.href='/painel/clientes'">
    <i class="fas fa-exclamation-triangle" style="font-size:20px;"></i>
    <span>
        <strong>Atenção:</strong> <?= $dados['clientes_em_atraso'] ?> cliente(s) com licença vencida ou próxima do vencimento. Clique para ver.
    </span>
</div>
<?php endif; ?>

<div class="dashboard-grid">
    <div class="row-top">
        <div class="card card-large">
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
        <div class="card card-large">
            <h3>Receita Mensal (R$)</h3>
            <canvas id="chartReceitaMensal"></canvas>
        </div>
    </div>
    <div class="row-bottom">
        <div class="card card-large">
            <h3>Comissão Mensal (R$)</h3>
            <canvas id="chartComissao"></canvas>
        </div>
        <div class="small-cards-grid">
            <div class="card card-small" id="cardClientes" style="cursor:pointer;">
                <h3>Clientes</h3>
                <canvas id="chartClientes" class="chart-doughnut"></canvas>
                <p>Ativos: <?= $dados['clientes_ativos'] ?? 0 ?> | Inativos: <?= $dados['clientes_inativos'] ?? 0 ?></p>
            </div>
            <div class="card card-small" id="cardLicencas" style="cursor:pointer;">
                <h3>Licenças</h3>
                <canvas id="chartLicencas" class="chart-doughnut"></canvas>
                <p>Ativas: <?= $dados['licencas_ativas'] ?? 0 ?> | Expiradas: <?= $dados['licencas_expiradas'] ?? 0 ?></p>
            </div>
            <div class="card-destaque card-small" id="cardAtraso" style="cursor:pointer;">
                <h3>Clientes em Atraso</h3>
                <div class="big-number"><?= $dados['clientes_em_atraso'] ?? 0 ?></div>
                <p>Clique para ver detalhes</p>
            </div>
            <div class="card card-small">
                <h3>Licenças Geradas</h3>
                <canvas id="chartLicencasGeradas"></canvas>
            </div>
        </div>
    </div>
</div>

<div id="modalDetalhes" class="modal-overlay" style="display:none;">
    <div class="modal-content">
        <span class="modal-close" id="modalDetalhesClose">&times;</span>
        <h2 id="modalTitulo"></h2>
        <ul id="modalLista" style="max-height:400px; overflow-y:auto;"></ul>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const clientesDetalhes = <?= json_encode($dados['clientes_detalhes'] ?? []) ?>;
const licencasDetalhes = <?= json_encode($dados['licencas_detalhes'] ?? []) ?>;
const atrasoDetalhes = <?= json_encode($dados['atraso_detalhes'] ?? []) ?>;

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

document.getElementById('cardClientes').addEventListener('click', function() {
    const todos = clientesDetalhes.map(c => ({nome: c.nome}));
    abrirModal('Detalhes dos Clientes', todos);
});

document.getElementById('cardLicencas').addEventListener('click', function() {
    const todos = licencasDetalhes.map(l => ({nome: l.cliente_nome}));
    abrirModal('Detalhes das Licenças', todos);
});

document.getElementById('cardAtraso').addEventListener('click', function() {
    // CORRIGIDO: usa a.nome (retornado pelo model)
    const todos = atrasoDetalhes.map(a => ({nome: a.nome || a.cliente_nome || 'Sem nome'}));
    abrirModal('Clientes em Atraso', todos);
});

function calcularStepSize(array) {
    if (!array || array.length === 0) return 250;
    const maxVal = Math.max(...array);
    if (maxVal <= 500) return 100;
    if (maxVal <= 1000) return 250;
    if (maxVal <= 3000) return 500;
    if (maxVal <= 10000) return 1000;
    return 2000;
}

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
        maintainAspectRatio: true,
        plugins: { legend: { display: false } }
    }
});

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
        maintainAspectRatio: true,
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
        layout: { padding: { top: 10, bottom: 25 } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) { return 'R$ ' + value.toFixed(2); },
                    autoSkip: true,
                    stepSize: calcularStepSize(<?= json_encode($dados['receitaMensal'] ?? []) ?>)
                }
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
        layout: { padding: { top: 10, bottom: 25 } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) { return 'R$ ' + value.toFixed(2); },
                    autoSkip: true,
                    stepSize: calcularStepSize(<?= json_encode($dados['comissaoMensal'] ?? []) ?>)
                }
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
        layout: { padding: { top: 10, bottom: 25 } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1,
                    precision: 0,
                    autoSkip: true,
                    callback: function(value) { if (Math.floor(value) === value) return value; }
                }
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
        layout: { padding: { top: 10, bottom: 50 } },
        plugins: {
            tooltip: { mode: 'index', intersect: false },
            legend: { position: 'top', labels: { boxWidth: 12, padding: 8 } }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) { return 'R$ ' + value.toFixed(2); },
                    autoSkip: true,
                    stepSize: calcularStepSize(
                        (<?= json_encode($dados['receitaAnual1'] ?? []) ?>).concat(<?= json_encode($dados['receitaAnual2'] ?? []) ?>)
                    )
                }
            }
        }
    }
});
</script>

<?php require __DIR__ . '/../partials/dashboard_footer.php'; ?>