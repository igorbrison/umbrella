<?php
/**
 * Arquivo: Views/painel/clientes/listar.php
 * Função: VIEW de listagem de clientes (painel do representante).
 * 
 * Exibe uma tabela com todos os clientes vinculados ao representante logado.
 * 
 * Recursos:
 *   - Cabeçalhos clicáveis permitem ordenação por coluna (ordem crescente/descendente).
 *   - Barra de pesquisa por nome, CPF/CNPJ ou email.
 *   - Paginação de 10 em 10 registros.
 *   - Links para ações: Novo Cliente, Editar.
 *   - Exibe o valor total atual e a data de expiração da licença.
 *   - Status do cliente e alertas de vencimento.
 */

if (!isset($clientes) || !is_array($clientes)) {
    $clientes = [];
}

// Dados de paginação (enviados pelo controller)
$paginaAtual   = $paginacao['pagina_atual'] ?? 1;
$totalPaginas  = $paginacao['total_paginas'] ?? 1;

// Parâmetros atuais da URL (para manter ordenação, termo, etc.)
$colAtual = $ordenacaoAtual['coluna'] ?? 'id';
$dirAtual = $ordenacaoAtual['direcao'] ?? 'asc';
$termoAtual = $_GET['termo'] ?? '';

// Monta array base para query string (usado nos links)
$queryBase = $_GET;
unset($queryBase['pagina']); // a página será definida individualmente

/**
 * Gera URL de ordenação, mantendo termo de busca e resetando a página para 1.
 */
function urlOrdenacaoPainel(string $coluna, string $colAtual, string $dirAtual, array $queryBase): string {
    $novaDirecao = ($coluna === $colAtual && $dirAtual === 'asc') ? 'desc' : 'asc';
    $params = array_merge($queryBase, ['ordem' => $coluna, 'direcao' => $novaDirecao, 'pagina' => 1]);
    return '/painel/clientes?' . http_build_query($params);
}

function setaPainel(string $coluna, string $colAtual, string $dirAtual): string {
    if ($coluna !== $colAtual) return '';
    return $dirAtual === 'asc' ? ' ▲' : ' ▼';
}

$titulo = 'Meus Clientes';
require __DIR__ . '/../../partials/dashboard_header.php';
?>

<h1>Bem-vindo, <?= htmlspecialchars($_SESSION['representante_nome'] ?? 'Representante') ?></h1>
<p>
    <a href="/painel/clientes/criar" class="btn btn-primary">Novo Cliente</a>
</p>

<!-- Barra de pesquisa -->
<form method="GET" action="/painel/clientes" style="display:flex; gap:10px; margin-bottom:16px;">
    <input type="text" name="termo" placeholder="Buscar por nome, CPF/CNPJ ou email" value="<?= htmlspecialchars($termoAtual) ?>" style="flex:1;">
    <!-- mantém ordenação atual ao buscar -->
    <input type="hidden" name="ordem" value="<?= htmlspecialchars($colAtual) ?>">
    <input type="hidden" name="direcao" value="<?= htmlspecialchars($dirAtual) ?>">
    <button type="submit" class="btn-primary">Buscar</button>
    <a href="/painel/clientes" class="btn">Limpar</a>
</form>

<table>
    <tr>
        <th><a href="<?= urlOrdenacaoPainel('id', $colAtual, $dirAtual, $queryBase) ?>">ID<?= setaPainel('id', $colAtual, $dirAtual) ?></a></th>
        <th><a href="<?= urlOrdenacaoPainel('nome', $colAtual, $dirAtual, $queryBase) ?>">Nome<?= setaPainel('nome', $colAtual, $dirAtual) ?></a></th>
        <th><a href="<?= urlOrdenacaoPainel('cpf_cnpj', $colAtual, $dirAtual, $queryBase) ?>">CPF/CNPJ<?= setaPainel('cpf_cnpj', $colAtual, $dirAtual) ?></a></th>
        <th><a href="<?= urlOrdenacaoPainel('email', $colAtual, $dirAtual, $queryBase) ?>">Email<?= setaPainel('email', $colAtual, $dirAtual) ?></a></th>
        <th>Valor Total</th>
        <th>Expiração</th>
        <th><a href="<?= urlOrdenacaoPainel('ativo', $colAtual, $dirAtual, $queryBase) ?>">Status<?= setaPainel('ativo', $colAtual, $dirAtual) ?></a></th>
        <th>Ações</th>
    </tr>
    <?php foreach ($clientes as $c):
        $dataExp = $c['data_expiracao'] ? new DateTime($c['data_expiracao']) : null;
        $expirada = $dataExp ? $dataExp < new DateTime() : false;
        $alerta = !$expirada && $dataExp && (int)(new DateTime())->format('d') >= 28;
    ?>
    <tr>
        <td><?= $c['id'] ?></td>
        <td><?= htmlspecialchars($c['nome']) ?></td>
        <td><?= htmlspecialchars($c['cpf_cnpj']) ?></td>
        <td><?= htmlspecialchars($c['email']) ?></td>
        <td>R$ <?= number_format($c['valor_total_atual'] ?? 0, 2, ',', '.') ?></td>
        <td class="<?= $expirada ? 'expirada' : ($alerta ? 'alerta' : '') ?>">
            <?= $dataExp ? $dataExp->format('d/m/Y') : 'Sem licença' ?>
            <?php if ($alerta): ?> ⚠️<?php endif; ?>
        </td>
        <td class="<?= $c['ativo'] ? 'status-ativo' : 'status-inativo' ?>">
            <?= $c['ativo'] ? 'Ativo' : 'Inativo' ?>
        </td>
        <td>
            <a href="/painel/clientes/editar/<?= $c['id'] ?>" class="btn">Editar</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<!-- Paginação -->
<?php if ($totalPaginas > 1): ?>
<div class="paginacao">
    <?php if ($paginaAtual > 1): ?>
        <a href="/painel/clientes?<?= http_build_query(array_merge($queryBase, ['pagina' => $paginaAtual - 1])) ?>">&laquo; Anterior</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
        <?php if ($i == $paginaAtual): ?>
            <span class="pagina-atual"><?= $i ?></span>
        <?php else: ?>
            <a href="/painel/clientes?<?= http_build_query(array_merge($queryBase, ['pagina' => $i])) ?>"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>

    <?php if ($paginaAtual < $totalPaginas): ?>
        <a href="/painel/clientes?<?= http_build_query(array_merge($queryBase, ['pagina' => $paginaAtual + 1])) ?>">Próximo &raquo;</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../../partials/dashboard_footer.php'; ?>