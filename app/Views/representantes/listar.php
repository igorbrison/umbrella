<?php
/**
 * Arquivo: Views/representantes/listar.php
 * Função: VIEW da lista de representantes com paginação.
 */

$titulo = 'Representantes';
require __DIR__ . '/../partials/dashboard_header.php';

if (!isset($representantes) || !is_array($representantes)) {
    $representantes = [];
}

$colAtual = $ordenacaoAtual['coluna'] ?? 'id';
$dirAtual = $ordenacaoAtual['direcao'] ?? 'asc';
$paginaAtual = $paginacao['pagina_atual'] ?? 1;
$totalPaginas = $paginacao['total_paginas'] ?? 1;
$queryBase = $_GET;
unset($queryBase['pagina']);

function urlOrdenacaoRepresentantes(string $coluna, string $colAtual, string $dirAtual): string {
    $novaDirecao = ($coluna === $colAtual && $dirAtual === 'asc') ? 'desc' : 'asc';
    return "?ordem=$coluna&direcao=$novaDirecao";
}

function setaRepresentantes(string $coluna, string $colAtual, string $dirAtual): string {
    if ($coluna !== $colAtual) return '';
    return $dirAtual === 'asc' ? ' ▲' : ' ▼';
}
?>

<h1>Representantes</h1>
<div class="barra-pesquisa">
    <a href="/admin/representantes/criar" class="btn btn-primary" style="flex: 0 0 auto;">Novo Representante</a>
</div>

<div class="tabela-responsiva">
    <table>
        <thead>
            <tr>
                <th><a href="<?= urlOrdenacaoRepresentantes('id', $colAtual, $dirAtual) ?>">ID<?= setaRepresentantes('id', $colAtual, $dirAtual) ?></a></th>
                <th><a href="<?= urlOrdenacaoRepresentantes('nome_razao', $colAtual, $dirAtual) ?>">Razão Social<?= setaRepresentantes('nome_razao', $colAtual, $dirAtual) ?></a></th>
                <th><a href="<?= urlOrdenacaoRepresentantes('nome_fantasia', $colAtual, $dirAtual) ?>">Nome Fantasia<?= setaRepresentantes('nome_fantasia', $colAtual, $dirAtual) ?></a></th>
                <th><a href="<?= urlOrdenacaoRepresentantes('cnpj', $colAtual, $dirAtual) ?>">CNPJ<?= setaRepresentantes('cnpj', $colAtual, $dirAtual) ?></a></th>
                <th><a href="<?= urlOrdenacaoRepresentantes('email', $colAtual, $dirAtual) ?>">Email<?= setaRepresentantes('email', $colAtual, $dirAtual) ?></a></th>
                <th><a href="<?= urlOrdenacaoRepresentantes('comissao_percentual', $colAtual, $dirAtual) ?>">Comissão (%)<?= setaRepresentantes('comissao_percentual', $colAtual, $dirAtual) ?></a></th>
                <th><a href="<?= urlOrdenacaoRepresentantes('ativo', $colAtual, $dirAtual) ?>">Status<?= setaRepresentantes('ativo', $colAtual, $dirAtual) ?></a></th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($representantes as $r): ?>
            <tr>
                <td data-label="ID"><?= $r['id'] ?></td>
                <td data-label="Razão Social"><?= htmlspecialchars($r['nome_razao']) ?></td>
                <td data-label="Nome Fantasia"><?= htmlspecialchars($r['nome_fantasia']) ?></td>
                <td data-label="CNPJ"><?= htmlspecialchars($r['cnpj']) ?></td>
                <td data-label="Email"><?= htmlspecialchars($r['email']) ?></td>
                <td data-label="Comissão (%)"><?= $r['comissao_percentual'] !== null ? number_format($r['comissao_percentual'], 2, ',', '.') . '%' : '-' ?></td>
                <td data-label="Status"><?= $r['ativo'] ? 'Ativo' : 'Inativo' ?></td>
                <td data-label="Ações" class="acoes-cell">
                    <a href="/admin/representantes/editar/<?= $r['id'] ?>" class="btn">Editar</a>
                    <a href="/admin/representantes/status/<?= $r['id'] ?>" class="btn"><?= $r['ativo'] ? 'Desativar' : 'Ativar' ?></a>
                    <a href="/admin/representantes/excluir/<?= $r['id'] ?>" class="btn"
                       onclick="event.preventDefault(); confirmarAcao('Tem certeza que deseja excluir este representante?', this.href)">Excluir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($totalPaginas > 1): ?>
<div class="paginacao">
    <?php if ($paginaAtual > 1): ?>
        <a href="/admin/representantes?<?= http_build_query(array_merge($queryBase, ['pagina' => $paginaAtual - 1])) ?>">&laquo; Anterior</a>
    <?php endif; ?>
    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
        <?php if ($i == $paginaAtual): ?>
            <span class="pagina-atual"><?= $i ?></span>
        <?php else: ?>
            <a href="/admin/representantes?<?= http_build_query(array_merge($queryBase, ['pagina' => $i])) ?>"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>
    <?php if ($paginaAtual < $totalPaginas): ?>
        <a href="/admin/representantes?<?= http_build_query(array_merge($queryBase, ['pagina' => $paginaAtual + 1])) ?>">Próximo &raquo;</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../partials/dashboard_footer.php'; ?>