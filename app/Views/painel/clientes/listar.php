<?php
/**
 * Arquivo: Views/painel/clientes/listar.php
 * Função: VIEW de listagem de clientes (painel do representante).
 * 
 * Exibe uma tabela com todos os clientes vinculados ao representante logado.
 * 
 * Recursos:
 *   - Cabeçalhos clicáveis permitem ordenação por coluna (ordem crescente/descendente).
 *   - Links para ações: Novo Cliente, Editar.
 *   - Exibe o valor total atual e a data de expiração da licença.
 *   - Status do cliente e alertas de vencimento.
 */

if (!isset($clientes) || !is_array($clientes)) {
    $clientes = [];
}

$colAtual = $ordenacaoAtual['coluna'] ?? 'id';
$dirAtual = $ordenacaoAtual['direcao'] ?? 'asc';

function urlOrdenacaoPainel(string $coluna, string $colAtual, string $dirAtual): string {
    $novaDirecao = ($coluna === $colAtual && $dirAtual === 'asc') ? 'desc' : 'asc';
    return "?ordem=$coluna&direcao=$novaDirecao";
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

<table>
    <tr>
        <th><a href="<?= urlOrdenacaoPainel('id', $colAtual, $dirAtual) ?>">ID<?= setaPainel('id', $colAtual, $dirAtual) ?></a></th>
        <th><a href="<?= urlOrdenacaoPainel('nome', $colAtual, $dirAtual) ?>">Nome<?= setaPainel('nome', $colAtual, $dirAtual) ?></a></th>
        <th><a href="<?= urlOrdenacaoPainel('cpf_cnpj', $colAtual, $dirAtual) ?>">CPF/CNPJ<?= setaPainel('cpf_cnpj', $colAtual, $dirAtual) ?></a></th>
        <th><a href="<?= urlOrdenacaoPainel('email', $colAtual, $dirAtual) ?>">Email<?= setaPainel('email', $colAtual, $dirAtual) ?></a></th>
        <th>Valor Total</th>
        <th>Expiração</th>
        <th><a href="<?= urlOrdenacaoPainel('ativo', $colAtual, $dirAtual) ?>">Status<?= setaPainel('ativo', $colAtual, $dirAtual) ?></a></th>
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

<?php require __DIR__ . '/../../partials/dashboard_footer.php'; ?>