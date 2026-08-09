<?php
/**
 * Arquivo: Views/admin/clientes/listar.php
 * Função: Listagem unificada de Clientes (Admin).
 * 
 * Exibe todos os clientes com informações de licença, representante,
 * busca, ordenação por colunas e paginação.
 * 
 * Ações disponíveis: Renovar licença, Gerar Token, Editar Cliente, Pagar.
 * Mobile: visualização em cards.
 */

if (!isset($licencas) || !is_array($licencas)) {
    $licencas = [];
}

// Paginação
$paginaAtual = $paginacao['pagina_atual'] ?? 1;
$totalPaginas = $paginacao['total_paginas'] ?? 1;

// Ordenação
$colAtual = $ordenacaoAtual['coluna'] ?? 'cliente_nome';
$dirAtual = $ordenacaoAtual['direcao'] ?? 'asc';
$termoAtual = $termoAtual ?? '';

$queryBase = $_GET;
unset($queryBase['pagina']);

// Funções para cabeçalhos ordenáveis
function urlOrdenacaoClientesAdmin(string $coluna, string $colAtual, string $dirAtual, array $queryBase): string {
    $novaDirecao = ($coluna === $colAtual && $dirAtual === 'asc') ? 'desc' : 'asc';
    $params = array_merge($queryBase, ['ordem' => $coluna, 'direcao' => $novaDirecao, 'pagina' => 1]);
    return '/admin/clientes?' . http_build_query($params);
}

function setaClientesAdmin(string $coluna, string $colAtual, string $dirAtual): string {
    if ($coluna !== $colAtual) return '';
    return $dirAtual === 'asc' ? ' ▲' : ' ▼';
}

// Mensagens de token
$tokenGerado = $tokenGerado ?? null;
$erroToken = $erroToken ?? null;

$titulo = 'Clientes';
require __DIR__ . '/../../partials/dashboard_header.php';
?>

<h1>Clientes</h1>

<?php if ($tokenGerado): ?>
    <div class="mensagem-sucesso">
        <strong>Token Offline Gerado:</strong><br>
        <textarea rows="3" readonly style="width:100%;"><?= htmlspecialchars($tokenGerado) ?></textarea>
    </div>
<?php endif; ?>

<?php if ($erroToken): ?>
    <div class="mensagem erro"><?= htmlspecialchars($erroToken) ?></div>
<?php endif; ?>

<!-- Barra de pesquisa -->
<form method="GET" action="/admin/clientes" class="barra-pesquisa">
    <input type="text" name="termo" placeholder="Buscar por nome, CPF/CNPJ ou email" value="<?= htmlspecialchars($termoAtual) ?>" class="campo-pesquisa">
    <input type="hidden" name="ordem" value="<?= htmlspecialchars($colAtual) ?>">
    <input type="hidden" name="direcao" value="<?= htmlspecialchars($dirAtual) ?>">
    <button type="submit" class="btn-primary">Buscar</button>
    <a href="/admin/clientes" class="btn btn-limpar">Limpar</a>
</form>

<div class="tabela-responsiva">
    <table>
        <thead>
            <tr>
                <th><a href="<?= urlOrdenacaoClientesAdmin('cliente_nome', $colAtual, $dirAtual, $queryBase) ?>">Cliente<?= setaClientesAdmin('cliente_nome', $colAtual, $dirAtual) ?></a></th>
                <th><a href="<?= urlOrdenacaoClientesAdmin('cpf_cnpj', $colAtual, $dirAtual, $queryBase) ?>">CPF/CNPJ<?= setaClientesAdmin('cpf_cnpj', $colAtual, $dirAtual) ?></a></th>
                <th><a href="<?= urlOrdenacaoClientesAdmin('representante_nome', $colAtual, $dirAtual, $queryBase) ?>">Representante<?= setaClientesAdmin('representante_nome', $colAtual, $dirAtual) ?></a></th>
                <th>Chave</th>
                <th><a href="<?= urlOrdenacaoClientesAdmin('data_expiracao', $colAtual, $dirAtual, $queryBase) ?>">Expiração<?= setaClientesAdmin('data_expiracao', $colAtual, $dirAtual) ?></a></th>
                <th><a href="<?= urlOrdenacaoClientesAdmin('valor_total_atual', $colAtual, $dirAtual, $queryBase) ?>">Valor Total<?= setaClientesAdmin('valor_total_atual', $colAtual, $dirAtual) ?></a></th>
                <th><a href="<?= urlOrdenacaoClientesAdmin('ativa', $colAtual, $dirAtual, $queryBase) ?>">Status<?= setaClientesAdmin('ativa', $colAtual, $dirAtual) ?></a></th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($licencas as $l):
                $dataExp = new DateTime($l['data_expiracao']);
                $hoje = new DateTime();
                $expirada = $dataExp < $hoje;
                $diaAtual = (int)$hoje->format('d');
                $alerta = !$expirada && $diaAtual >= 28;
            ?>
            <tr>
                <td data-label="Cliente"><?= htmlspecialchars($l['cliente_nome']) ?></td>
                <td data-label="CPF/CNPJ"><?= htmlspecialchars($l['cpf_cnpj']) ?></td>
                <td data-label="Representante"><?= htmlspecialchars($l['representante_nome']) ?></td>
                <td data-label="Chave">
                    <code style="cursor:pointer;" onclick="verChave('<?= htmlspecialchars($l['chave']) ?>')">
                        <?= substr($l['chave'], 0, 16) ?>...
                    </code>
                </td>
                <td data-label="Expiração" class="<?= $expirada ? 'expirada' : ($alerta ? 'alerta' : '') ?>">
                    <?= $dataExp->format('d/m/Y') ?>
                    <?php if ($alerta): ?> ⚠️<?php endif; ?>
                </td>
                <td data-label="Valor Total">R$ <?= number_format($l['valor_total'] ?? 0, 2, ',', '.') ?></td>
                <td data-label="Status" class="<?= $l['ativa'] && !$expirada ? 'status-ativo' : 'status-inativo' ?>">
                    <?= $l['ativa'] && !$expirada ? 'Ativa' : 'Expirada/Inativa' ?>
                </td>
                <td data-label="Ações">
                    <a href="/admin/clientes/renovar/<?= $l['cliente_id'] ?>" class="btn" onclick="return confirm('Renovar licença?')">Renovar</a>
                    <a href="/admin/clientes/gerar-token/<?= $l['cliente_id'] ?>" class="btn" onclick="return confirm('Gerar token offline?')">Gerar Token</a>
                    <a href="/admin/clientes/editar/<?= $l['cliente_id'] ?>" class="btn">Editar</a>
                    <button type="button" class="btn btn-pagar"
                            data-cliente-id="<?= $l['cliente_id'] ?>"
                            data-nome="<?= htmlspecialchars($l['cliente_nome']) ?>">
                        Pagar
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Paginação -->
<?php if ($totalPaginas > 1): ?>
<div class="paginacao">
    <?php if ($paginaAtual > 1): ?>
        <a href="/admin/clientes?<?= http_build_query(array_merge($queryBase, ['pagina' => $paginaAtual - 1])) ?>">&laquo; Anterior</a>
    <?php endif; ?>
    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
        <?php if ($i == $paginaAtual): ?>
            <span class="pagina-atual"><?= $i ?></span>
        <?php else: ?>
            <a href="/admin/clientes?<?= http_build_query(array_merge($queryBase, ['pagina' => $i])) ?>"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>
    <?php if ($paginaAtual < $totalPaginas): ?>
        <a href="/admin/clientes?<?= http_build_query(array_merge($queryBase, ['pagina' => $paginaAtual + 1])) ?>">Próximo &raquo;</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Modal de pagamento -->
<div id="modalPagamento" class="modal-overlay" style="display:none;">
    <div class="modal-content">
        <span class="modal-close" id="modalPagamentoClose">&times;</span>
        <h2>Registrar Pagamento</h2>
        <form method="POST" action="/admin/clientes/pagar">
            <input type="hidden" name="cliente_id" id="pg-cliente-id">
            <div class="input-group">
                <label>Cliente: <span id="pg-cliente-nome" style="font-weight:bold;"></span></label>
            </div>
            <div class="input-group">
                <label for="pg-valor">Valor (R$)</label>
                <input type="number" step="0.01" name="valor" id="pg-valor" required>
            </div>
            <div class="input-group">
                <label for="pg-data">Data do Pagamento</label>
                <input type="date" name="data_pagamento" id="pg-data" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="input-group">
                <label for="pg-mes">Mês de Referência</label>
                <input type="month" name="mes_referencia" id="pg-mes" value="<?= date('Y-m') ?>" required>
            </div>
            <div class="input-group">
                <label for="pg-obs">Observação</label>
                <input type="text" name="observacao" id="pg-obs">
            </div>
            <button type="submit" class="btn-primary">Registrar Pagamento</button>
        </form>
    </div>
</div>

<script>
// Modal de pagamento
document.querySelectorAll('.btn-pagar').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('pg-cliente-id').value = this.dataset.clienteId;
        document.getElementById('pg-cliente-nome').textContent = this.dataset.nome;
        document.getElementById('modalPagamento').style.display = 'flex';
    });
});

document.getElementById('modalPagamentoClose').addEventListener('click', function() {
    document.getElementById('modalPagamento').style.display = 'none';
});

// Função para exibir chave completa (usa o modal genérico do footer)
function verChave(chave) {
    document.getElementById('modalChaveTitulo').textContent = 'Chave da Licença';
    document.getElementById('chaveCompleta').value = chave;
    document.getElementById('modalChave').style.display = 'flex';
}
</script>

<?php require __DIR__ . '/../../partials/dashboard_footer.php'; ?>