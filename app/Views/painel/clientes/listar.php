<?php
/**
 * Arquivo: Views/painel/clientes/listar.php
 * Função: VIEW de listagem de clientes (painel do representante).
 * 
 * Exibe uma tabela com todos os clientes vinculados ao representante logado.
 * 
 * Recursos:
 *   - Cabeçalhos clicáveis permitem ordenação por coluna (ordem crescente/descendente).
 *   - Links para ações: Novo Cliente, Editar, Excluir.
 *   - Exibe o valor total atual de cada cliente (calculado dinamicamente com base
 *     nos módulos contratados e no salário mínimo vigente).
 *   - Saudação personalizada com o nome de exibição do representante.
 */

// Garante que a variável $clientes seja sempre um array
if (!isset($clientes) || !is_array($clientes)) {
    $clientes = [];
}

// Captura a coluna e direção atuais da ordenação, enviadas pelo Controller
$colAtual = $ordenacaoAtual['coluna'] ?? 'id';
$dirAtual = $ordenacaoAtual['direcao'] ?? 'asc';

/**
 * Função auxiliar: gera a URL de ordenação para cada cabeçalho da tabela.
 * - Se a coluna clicada já está ativa, alterna a direção (asc <-> desc).
 * - Caso contrário, inicia sempre com ascendente.
 */
function urlOrdenacaoPainel(string $coluna, string $colAtual, string $dirAtual): string {
    $novaDirecao = ($coluna === $colAtual && $dirAtual === 'asc') ? 'desc' : 'asc';
    return "?ordem=$coluna&direcao=$novaDirecao";
}

/**
 * Função auxiliar: exibe uma seta indicativa (▲ ou ▼) ao lado da coluna atualmente ordenada.
 */
function setaPainel(string $coluna, string $colAtual, string $dirAtual): string {
    if ($coluna !== $colAtual) return '';
    return $dirAtual === 'asc' ? ' ▲' : ' ▼';
}

// Título da página
$titulo = 'Meus Clientes';

// Inclui o cabeçalho comum (HTML, CSS, favicon)
require __DIR__ . '/../../partials/dashboard_header.php';
?>

<h1>Bem-vindo, <?= htmlspecialchars($_SESSION['representante_nome'] ?? 'Representante') ?></h1>
<p>
    <a href="/painel/clientes/criar" class="btn">Novo Cliente</a>
    <a href="/logout" class="btn">Sair</a>
</p>

<!-- Tabela de clientes com ordenação clicável -->
<table>
    <tr>
        <th><a href="<?= urlOrdenacaoPainel('id', $colAtual, $dirAtual) ?>">ID<?= setaPainel('id', $colAtual, $dirAtual) ?></a></th>
        <th><a href="<?= urlOrdenacaoPainel('nome', $colAtual, $dirAtual) ?>">Nome<?= setaPainel('nome', $colAtual, $dirAtual) ?></a></th>
        <th><a href="<?= urlOrdenacaoPainel('cpf_cnpj', $colAtual, $dirAtual) ?>">CPF/CNPJ<?= setaPainel('cpf_cnpj', $colAtual, $dirAtual) ?></a></th>
        <th><a href="<?= urlOrdenacaoPainel('email', $colAtual, $dirAtual) ?>">Email<?= setaPainel('email', $colAtual, $dirAtual) ?></a></th>
        <th>Valor Total</th>
        <th><a href="<?= urlOrdenacaoPainel('ativo', $colAtual, $dirAtual) ?>">Status<?= setaPainel('ativo', $colAtual, $dirAtual) ?></a></th>
        <th>Ações</th>
    </tr>
    <?php foreach ($clientes as $c): ?>
    <tr>
        <td><?= $c['id'] ?></td>
        <td><?= htmlspecialchars($c['nome']) ?></td>
        <td><?= htmlspecialchars($c['cpf_cnpj']) ?></td>
        <td><?= htmlspecialchars($c['email']) ?></td>
        <!-- Valor total calculado dinamicamente no controller (baseado no salário mínimo) -->
        <td>R$ <?= number_format($c['valor_total_atual'] ?? 0, 2, ',', '.') ?></td>
        <td><?= $c['ativo'] ? 'Ativo' : 'Inativo' ?></td>
        <td>
            <a href="/painel/clientes/editar/<?= $c['id'] ?>" class="btn">Editar</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<?php require __DIR__ . '/../../partials/dashboard_footer.php'; ?>