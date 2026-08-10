<?php
/**
 * Relatório de Pagamentos (Admin).
 */
$pagamentos      = $dados['pagamentos'] ?? [];
$somaPeriodo     = $dados['somaPeriodo'] ?? 0;
$representantes  = $dados['representantes'] ?? [];
$paginaAtual     = $dados['paginaAtual'] ?? 1;
$totalPaginas    = $dados['totalPaginas'] ?? 1;
$dataInicio      = $dados['dataInicio'] ?? '';
$dataFim         = $dados['dataFim'] ?? '';
$representanteId = $dados['representanteId'] ?? '';
$termo           = $dados['termo'] ?? '';

$queryBase = $_GET;
unset($queryBase['pagina']);

$titulo = 'Relatório de Pagamentos';
require __DIR__ . '/../../partials/dashboard_header.php';
?>

<h1>Relatório de Pagamentos</h1>

<!-- Filtros -->
<form method="GET" action="/admin/relatorios/pagamentos" class="barra-pesquisa-filtros">
    <input type="date" name="data_inicio" value="<?= htmlspecialchars($dataInicio) ?>" class="campo-pesquisa" style="max-width:150px;" placeholder="Data inicial">
    <input type="date" name="data_fim" value="<?= htmlspecialchars($dataFim) ?>" class="campo-pesquisa" style="max-width:150px;" placeholder="Data final">
    <select name="representante_id" class="campo-pesquisa" style="max-width:200px;">
        <option value="">Todos os representantes</option>
        <?php foreach ($representantes as $rep): ?>
            <option value="<?= $rep['id'] ?>" <?= $representanteId == $rep['id'] ? 'selected' : '' ?>><?= htmlspecialchars($rep['nome_razao']) ?></option>
        <?php endforeach; ?>
    </select>
    <input type="text" name="termo" value="<?= htmlspecialchars($termo) ?>" class="campo-pesquisa" placeholder="Buscar cliente...">
    <button type="submit" class="btn-primary">Filtrar</button>
    <a href="/admin/relatorios/pagamentos" class="btn btn-limpar">Limpar</a>
</form>

<!-- Totalizador alinhado -->
<div class="card card-totalizador" style="margin-bottom:16px; padding:12px 20px; display: flex; flex-wrap: wrap; align-items: center; gap: 20px;">
    <span><strong>Total no período:</strong> R$ <?= number_format($somaPeriodo, 2, ',', '.') ?></span>
    <span><strong>Registros:</strong> <?= $dados['total'] ?? 0 ?></span>
</div>

<div class="tabela-responsiva">
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Cliente</th>
                <th>Representante</th>
                <th>Valor (R$)</th>
                <th>Mês Ref.</th>
                <th>Observação</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pagamentos as $p): ?>
            <tr>
                <td data-label="Data"><?= date('d/m/Y', strtotime($p['data_pagamento'])) ?></td>
                <td data-label="Cliente"><?= htmlspecialchars($p['cliente_nome']) ?></td>
                <td data-label="Representante"><?= htmlspecialchars($p['representante_nome']) ?></td>
                <td data-label="Valor (R$)">R$ <?= number_format($p['valor'], 2, ',', '.') ?></td>
                <td data-label="Mês Ref."><?= htmlspecialchars($p['mes_referencia']) ?></td>
                <td data-label="Observação"><?= htmlspecialchars($p['observacao'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($totalPaginas > 1): ?>
<div class="paginacao">
    <?php if ($paginaAtual > 1): ?>
        <a href="/admin/relatorios/pagamentos?<?= http_build_query(array_merge($queryBase, ['pagina' => $paginaAtual - 1])) ?>">&laquo; Anterior</a>
    <?php endif; ?>
    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
        <?php if ($i == $paginaAtual): ?>
            <span class="pagina-atual"><?= $i ?></span>
        <?php else: ?>
            <a href="/admin/relatorios/pagamentos?<?= http_build_query(array_merge($queryBase, ['pagina' => $i])) ?>"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>
    <?php if ($paginaAtual < $totalPaginas): ?>
        <a href="/admin/relatorios/pagamentos?<?= http_build_query(array_merge($queryBase, ['pagina' => $paginaAtual + 1])) ?>">Próximo &raquo;</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../../partials/dashboard_footer.php'; ?>