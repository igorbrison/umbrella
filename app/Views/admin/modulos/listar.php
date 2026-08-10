<?php
/**
 * Arquivo: Views/admin/modulos/listar.php
 * Função: VIEW de listagem de módulos (painel admin).
 * 
 * Exibe uma tabela com todos os módulos cadastrados, com ordenação por coluna,
 * paginação e ações de criar, editar e excluir. Mobile: visualização em cards.
 */

if (!isset($modulos) || !is_array($modulos)) {
    $modulos = [];
}

$paginaAtual = $paginacao['pagina_atual'] ?? 1;
$totalPaginas = $paginacao['total_paginas'] ?? 1;
$colAtual = $ordenacaoAtual['coluna'] ?? 'id';
$dirAtual = $ordenacaoAtual['direcao'] ?? 'asc';
$queryBase = $_GET;
unset($queryBase['pagina']);

function urlOrdenacaoModulos(string $coluna, string $colAtual, string $dirAtual): string {
    $novaDirecao = ($coluna === $colAtual && $dirAtual === 'asc') ? 'desc' : 'asc';
    return "?ordem=$coluna&direcao=$novaDirecao";
}

function setaModulos(string $coluna, string $colAtual, string $dirAtual): string {
    if ($coluna !== $colAtual) return '';
    return $dirAtual === 'asc' ? ' ▲' : ' ▼';
}

$titulo = 'Gerenciar Módulos';
require __DIR__ . '/../../partials/dashboard_header.php';
?>

<h1>Módulos</h1>
<div class="barra-pesquisa">
    <a href="/admin/modulos/criar" class="btn btn-primary" style="flex: 0 0 auto;">Novo Módulo</a>
</div>

<div class="tabela-responsiva">
    <table>
        <thead>
            <tr>
                <th><a href="<?= urlOrdenacaoModulos('nome', $colAtual, $dirAtual) ?>">Nome<?= setaModulos('nome', $colAtual, $dirAtual) ?></a></th>
                <th><a href="<?= urlOrdenacaoModulos('identificador', $colAtual, $dirAtual) ?>">Identificador<?= setaModulos('identificador', $colAtual, $dirAtual) ?></a></th>
                <th><a href="<?= urlOrdenacaoModulos('valor', $colAtual, $dirAtual) ?>">Valor (R$)<?= setaModulos('valor', $colAtual, $dirAtual) ?></a></th>
                <th><a href="<?= urlOrdenacaoModulos('ativo', $colAtual, $dirAtual) ?>">Ativo<?= setaModulos('ativo', $colAtual, $dirAtual) ?></a></th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($modulos as $m): ?>
            <tr>
                <td data-label="Nome"><?= htmlspecialchars($m['nome']) ?></td>
                <td data-label="Identificador"><?= htmlspecialchars($m['identificador']) ?></td>
                <td data-label="Valor (R$)">R$ <?= number_format($m['valor'], 2, ',', '.') ?></td>
                <td data-label="Ativo"><?= $m['ativo'] ? 'Sim' : 'Não' ?></td>
                <td data-label="Ações" class="acoes-cell">
                    <a href="/admin/modulos/editar/<?= $m['id'] ?>" class="btn">Editar</a>
                    <a href="/admin/modulos/excluir/<?= $m['id'] ?>" class="btn"
                       onclick="event.preventDefault(); confirmarAcao('Tem certeza que deseja excluir este módulo?', this.href)">Excluir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($totalPaginas > 1): ?>
<div class="paginacao">
    <?php if ($paginaAtual > 1): ?>
        <a href="/admin/modulos?<?= http_build_query(array_merge($queryBase, ['pagina' => $paginaAtual - 1])) ?>">&laquo; Anterior</a>
    <?php endif; ?>
    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
        <?php if ($i == $paginaAtual): ?>
            <span class="pagina-atual"><?= $i ?></span>
        <?php else: ?>
            <a href="/admin/modulos?<?= http_build_query(array_merge($queryBase, ['pagina' => $i])) ?>"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>
    <?php if ($paginaAtual < $totalPaginas): ?>
        <a href="/admin/modulos?<?= http_build_query(array_merge($queryBase, ['pagina' => $paginaAtual + 1])) ?>">Próximo &raquo;</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../../partials/dashboard_footer.php'; ?>