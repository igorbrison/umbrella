<?php
/**
 * Arquivo: Views/admin/modulos/listar.php
 * Função: VIEW de listagem de módulos (painel admin).
 * 
 * Exibe uma tabela com todos os módulos cadastrados, permitindo:
 *   - Ordenação por coluna clicando nos cabeçalhos.
 *   - Criar, editar e excluir módulos.
 *   - Visualizar o valor calculado de cada módulo com base no
 *     percentual do salário mínimo vigente.
 * 
 * Apenas o administrador tem acesso a esta tela.
 */

// Garante que a variável $modulos seja sempre um array
if (!isset($modulos) || !is_array($modulos)) {
    $modulos = [];
}

// Captura a coluna e direção atuais da ordenação, enviadas pelo Controller
$colAtual = $ordenacaoAtual['coluna'] ?? 'id';
$dirAtual = $ordenacaoAtual['direcao'] ?? 'asc';

/**
 * Função auxiliar: gera a URL de ordenação para cada cabeçalho da tabela.
 */
function urlOrdenacaoModulos(string $coluna, string $colAtual, string $dirAtual): string {
    $novaDirecao = ($coluna === $colAtual && $dirAtual === 'asc') ? 'desc' : 'asc';
    return "?ordem=$coluna&direcao=$novaDirecao";
}

/**
 * Função auxiliar: exibe uma seta indicativa (▲ ou ▼) ao lado da coluna ativa.
 */
function setaModulos(string $coluna, string $colAtual, string $dirAtual): string {
    if ($coluna !== $colAtual) return '';
    return $dirAtual === 'asc' ? ' ▲' : ' ▼';
}

// Título da página
$titulo = 'Gerenciar Módulos';

// Inclui o cabeçalho comum (HTML, CSS, favicon)
require __DIR__ . '/../partials/dashboard_header.php';
?>

<h1>Módulos</h1>
<p>
    <a href="/admin/modulos/criar" class="btn">Novo Módulo</a>
    <a href="/admin/representantes" class="btn">Voltar para Representantes</a>
</p>

<!-- Tabela de módulos com ordenação clicável -->
<table>
    <tr>
        <th><a href="<?= urlOrdenacaoModulos('id', $colAtual, $dirAtual) ?>">ID<?= setaModulos('id', $colAtual, $dirAtual) ?></a></th>
        <th><a href="<?= urlOrdenacaoModulos('identificador', $colAtual, $dirAtual) ?>">Identificador<?= setaModulos('identificador', $colAtual, $dirAtual) ?></a></th>
        <th><a href="<?= urlOrdenacaoModulos('nome', $colAtual, $dirAtual) ?>">Nome<?= setaModulos('nome', $colAtual, $dirAtual) ?></a></th>
        <th><a href="<?= urlOrdenacaoModulos('valor', $colAtual, $dirAtual) ?>">Valor (R$)<?= setaModulos('valor', $colAtual, $dirAtual) ?></a></th>
        <th><a href="<?= urlOrdenacaoModulos('ativo', $colAtual, $dirAtual) ?>">Ativo<?= setaModulos('ativo', $colAtual, $dirAtual) ?></a></th>
        <th>Ações</th>
    </tr>
    <?php foreach ($modulos as $m): ?>
    <tr>
        <td><?= $m['id'] ?></td>
        <td><?= htmlspecialchars($m['identificador']) ?></td>
        <td><?= htmlspecialchars($m['nome']) ?></td>
        <!-- Valor calculado dinamicamente pelo controller (Model) -->
        <td>R$ <?= number_format($m['valor'], 2, ',', '.') ?></td>
        <td><?= $m['ativo'] ? 'Sim' : 'Não' ?></td>
        <td>
            <a href="/admin/modulos/editar/<?= $m['id'] ?>" class="btn">Editar</a>
            <a href="/admin/modulos/excluir/<?= $m['id'] ?>" class="btn" onclick="return confirm('Tem certeza que deseja excluir este módulo?')">Excluir</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<?php require __DIR__ . '/../partials/dashboard_footer.php'; ?>