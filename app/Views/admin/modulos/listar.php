<?php
/**
 * Arquivo: Views/admin/modulos/listar.php
 * Função: VIEW de listagem de módulos (painel admin).
 * 
 * Exibe uma tabela com todos os módulos cadastrados, com ordenação por coluna
 * e ações de criar, editar e excluir. Mobile: visualização em cards.
 */

if (!isset($modulos) || !is_array($modulos)) {
    $modulos = [];
}

$colAtual = $ordenacaoAtual['coluna'] ?? 'id';
$dirAtual = $ordenacaoAtual['direcao'] ?? 'asc';

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
<p>
    <a href="/admin/modulos/criar" class="btn btn-primary">Novo Módulo</a>
</p>

<div class="tabela-responsiva">
    <table>
        <thead>
            <tr>
                <th><a href="<?= urlOrdenacaoModulos('id', $colAtual, $dirAtual) ?>">ID<?= setaModulos('id', $colAtual, $dirAtual) ?></a></th>
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
                <td data-label="ID"><?= $m['id'] ?></td>
                <td data-label="Nome"><?= htmlspecialchars($m['nome']) ?></td>
                <td data-label="Identificador"><?= htmlspecialchars($m['identificador']) ?></td>
                <td data-label="Valor (R$)">R$ <?= number_format($m['valor'], 2, ',', '.') ?></td>
                <td data-label="Ativo"><?= $m['ativo'] ? 'Sim' : 'Não' ?></td>
                <td data-label="Ações">
                    <a href="/admin/modulos/editar/<?= $m['id'] ?>" class="btn">Editar</a>
                    <a href="/admin/modulos/excluir/<?= $m['id'] ?>" class="btn" onclick="return confirm('Tem certeza que deseja excluir este módulo?')">Excluir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../../partials/dashboard_footer.php'; ?>