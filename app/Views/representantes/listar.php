<?php
/**
 * Arquivo: Views/representantes/listar.php
 * Função: VIEW da lista de representantes.
 * 
 * Características:
 *   - Exibe uma tabela com todos os representantes cadastrados.
 *   - Cabeçalhos clicáveis permitem ordenação por coluna.
 *   - Links para ações: Criar, Editar, Ativar/Desativar, Excluir.
 *   - Mobile: visualização em cards.
 */

$titulo = 'Representantes';
require __DIR__ . '/../partials/dashboard_header.php';

if (!isset($representantes) || !is_array($representantes)) {
    $representantes = [];
}

$colAtual = $ordenacaoAtual['coluna'] ?? 'id';
$dirAtual = $ordenacaoAtual['direcao'] ?? 'asc';

function urlOrdenacao(string $coluna, string $colAtual, string $dirAtual): string {
    $novaDirecao = ($coluna === $colAtual && $dirAtual === 'asc') ? 'desc' : 'asc';
    return "?ordem=$coluna&direcao=$novaDirecao";
}

function seta(string $coluna, string $colAtual, string $dirAtual): string {
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
                <th><a href="<?= urlOrdenacao('id', $colAtual, $dirAtual) ?>">ID<?= seta('id', $colAtual, $dirAtual) ?></a></th>
                <th><a href="<?= urlOrdenacao('nome_razao', $colAtual, $dirAtual) ?>">Razão Social<?= seta('nome_razao', $colAtual, $dirAtual) ?></a></th>
                <th><a href="<?= urlOrdenacao('nome_fantasia', $colAtual, $dirAtual) ?>">Nome Fantasia<?= seta('nome_fantasia', $colAtual, $dirAtual) ?></a></th>
                <th><a href="<?= urlOrdenacao('cnpj', $colAtual, $dirAtual) ?>">CNPJ<?= seta('cnpj', $colAtual, $dirAtual) ?></a></th>
                <th><a href="<?= urlOrdenacao('email', $colAtual, $dirAtual) ?>">Email<?= seta('email', $colAtual, $dirAtual) ?></a></th>
                <th><a href="<?= urlOrdenacao('comissao_percentual', $colAtual, $dirAtual) ?>">Comissão (%)<?= seta('comissao_percentual', $colAtual, $dirAtual) ?></a></th>
                <th><a href="<?= urlOrdenacao('ativo', $colAtual, $dirAtual) ?>">Status<?= seta('ativo', $colAtual, $dirAtual) ?></a></th>
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

<?php require __DIR__ . '/../partials/dashboard_footer.php'; ?>