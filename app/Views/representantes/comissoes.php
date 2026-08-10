<?php
/**
 * Tela de comissões devidas aos representantes (admin).
 */
$mesReferencia = $_GET['mes'] ?? date('Y-m', strtotime('first day of last month'));
$filtroStatus = $_GET['status'] ?? 'todos';
$dataFormatada = DateTime::createFromFormat('Y-m', $mesReferencia)->format('m/Y');
$titulo = 'Comissões – ' . $dataFormatada;
require __DIR__ . '/../partials/dashboard_header.php';

$representantes = $representantes ?? [];
$paginaAtual = $paginacao['pagina_atual'] ?? 1;
$totalPaginas = $paginacao['total_paginas'] ?? 1;
$queryBase = $_GET;
unset($queryBase['pagina']);
?>

<h1><?= htmlspecialchars($titulo) ?></h1>

<!-- Filtros -->
<form method="GET" action="/admin/representantes/comissoes" class="barra-pesquisa-filtros">
    <input type="month" name="mes" value="<?= $mesReferencia ?>" class="campo-pesquisa" style="max-width:180px;">
    <select name="status" class="campo-pesquisa" style="max-width:180px;">
        <option value="todos" <?= $filtroStatus == 'todos' ? 'selected' : '' ?>>Todos</option>
        <option value="pendentes" <?= $filtroStatus == 'pendentes' ? 'selected' : '' ?>>Pendentes</option>
        <option value="pagos" <?= $filtroStatus == 'pagos' ? 'selected' : '' ?>>Pagos</option>
    </select>
    <button type="submit" class="btn-primary">Filtrar</button>
</form>

<div class="tabela-responsiva">
    <table>
        <thead>
            <tr>
                <th>Representante</th>
                <th>Comissão (%)</th>
                <th>Comissão Devida (R$)</th>
                <th>Já Pago (R$)</th>
                <th>Saldo (R$)</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($representantes as $r): ?>
            <tr>
                <td data-label="Representante"><?= htmlspecialchars($r['nome_razao']) ?></td>
                <td data-label="Comissão (%)"><?= number_format($r['comissao_percentual'], 2, ',', '.') ?>%</td>
                <td data-label="Devida (R$)">R$ <?= number_format($r['comissao_devida'], 2, ',', '.') ?></td>
                <td data-label="Já Pago (R$)">R$ <?= number_format($r['comissao_paga'], 2, ',', '.') ?></td>
                <td data-label="Saldo (R$)">R$ <?= number_format($r['comissao_devida'] - $r['comissao_paga'], 2, ',', '.') ?></td>
                <td data-label="Ações" class="acoes-cell">
                    <?php if (($r['comissao_devida'] - $r['comissao_paga']) > 0): ?>
                        <button type="button" class="btn btn-pagar-comissao"
                                data-id="<?= $r['id'] ?>"
                                data-nome="<?= htmlspecialchars($r['nome_razao']) ?>"
                                data-valor="<?= $r['comissao_devida'] - $r['comissao_paga'] ?>">
                            Pagar
                        </button>
                    <?php endif; ?>
                    <a href="/admin/representantes/comissoes/relatorio/<?= $r['id'] ?>?mes=<?= $mesReferencia ?>" target="_blank" class="btn">Relatório</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($totalPaginas > 1): ?>
<div class="paginacao">
    <?php if ($paginaAtual > 1): ?>
        <a href="/admin/representantes/comissoes?<?= http_build_query(array_merge($queryBase, ['pagina' => $paginaAtual - 1])) ?>">&laquo; Anterior</a>
    <?php endif; ?>
    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
        <?php if ($i == $paginaAtual): ?>
            <span class="pagina-atual"><?= $i ?></span>
        <?php else: ?>
            <a href="/admin/representantes/comissoes?<?= http_build_query(array_merge($queryBase, ['pagina' => $i])) ?>"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>
    <?php if ($paginaAtual < $totalPaginas): ?>
        <a href="/admin/representantes/comissoes?<?= http_build_query(array_merge($queryBase, ['pagina' => $paginaAtual + 1])) ?>">Próximo &raquo;</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Modal de pagamento de comissão (mantido) -->
<div id="modalComissao" class="modal-overlay" style="display:none;">
    <div class="modal-content">
        <span class="modal-close" id="modalComissaoClose">&times;</span>
        <h2>Registrar Pagamento de Comissão</h2>
        <form method="POST" action="/admin/representantes/pagar-comissao">
            <input type="hidden" name="representante_id" id="comissao-representante-id">
            <input type="hidden" name="mes_referencia" value="<?= $mesReferencia ?>">
            <div class="input-group">
                <label>Representante: <span id="comissao-representante-nome" style="font-weight:bold;"></span></label>
            </div>
            <div class="input-group">
                <label for="comissao-valor">Valor (R$)</label>
                <input type="number" step="0.01" name="valor" id="comissao-valor" required>
            </div>
            <div class="input-group">
                <label for="comissao-obs">Observação (ex: PIX, Boleto)</label>
                <input type="text" name="observacao" id="comissao-obs">
            </div>
            <button type="submit" class="btn-primary">Confirmar Pagamento</button>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.btn-pagar-comissao').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('comissao-representante-id').value = this.dataset.id;
        document.getElementById('comissao-representante-nome').textContent = this.dataset.nome;
        document.getElementById('comissao-valor').value = this.dataset.valor;
        document.getElementById('modalComissao').style.display = 'flex';
    });
});
document.getElementById('modalComissaoClose').addEventListener('click', function() {
    document.getElementById('modalComissao').style.display = 'none';
});
</script>

<?php require __DIR__ . '/../partials/dashboard_footer.php'; ?>