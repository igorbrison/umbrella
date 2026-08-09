<?php
/**
 * Arquivo: Views/representantes/listar.php
 * Função: VIEW da lista de representantes.
 * 
 * Características:
 *   - Exibe uma tabela com todos os representantes cadastrados.
 *   - Cabeçalhos clicáveis permitem ordenação por coluna (ordem crescente/descendente).
 *   - Links para ações: Criar, Editar, Ativar/Desativar, Excluir.
 *   - Apenas o Administrador (dono da empresa) tem acesso a esta tela.
 */

// Título da página (aparecerá na aba do navegador)
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
    <a href="/admin/representantes/criar" class="btn btn-primary">Novo Representante</a>
</div>

<table>
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
    <?php foreach ($representantes as $r): ?>
    <tr>
        <td><?= $r['id'] ?></td>
        <td><?= htmlspecialchars($r['nome_razao']) ?></td>
        <td><?= htmlspecialchars($r['nome_fantasia']) ?></td>
        <td><?= htmlspecialchars($r['cnpj']) ?></td>
        <td><?= htmlspecialchars($r['email']) ?></td>
        <td><?= $r['comissao_percentual'] !== null ? number_format($r['comissao_percentual'], 2, ',', '.') . '%' : '-' ?></td>
        <td><?= $r['ativo'] ? 'Ativo' : 'Inativo' ?></td>
        <td>
            <a href="/admin/representantes/editar/<?= $r['id'] ?>" class="btn">Editar</a>
            <a href="/admin/representantes/status/<?= $r['id'] ?>" class="btn"><?= $r['ativo'] ? 'Desativar' : 'Ativar' ?></a>
            <a href="/admin/representantes/excluir/<?= $r['id'] ?>" class="btn" onclick="return confirm('Tem certeza?')">Excluir</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<?php require __DIR__ . '/../partials/dashboard_footer.php'; ?>