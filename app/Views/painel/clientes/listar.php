<?php
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
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Meus Clientes</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; }
        th { background: #f5f5f5; }
        th a { text-decoration: none; color: inherit; display: block; }
        th a:hover { text-decoration: underline; }
        .btn { padding: 5px 10px; text-decoration: none; background: #eee; border:1px solid #aaa; border-radius:3px; margin-right: 5px; }
    </style>
</head>
<body>
    <h1>Bem-vindo, <?= htmlspecialchars($_SESSION['representante_nome'] ?? 'Representante') ?></h1>
    <p>
        <a href="/painel/clientes/criar" class="btn">Novo Cliente</a>
        <a href="/logout" class="btn">Sair</a>
    </p>
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
            <!-- Agora usa o valor total atualizado calculado no controller -->
            <td>R$ <?= number_format($c['valor_total_atual'] ?? 0, 2, ',', '.') ?></td>
            <td><?= $c['ativo'] ? 'Ativo' : 'Inativo' ?></td>
            <td>
                <a href="/painel/clientes/editar/<?= $c['id'] ?>" class="btn">Editar</a>
                <a href="/painel/clientes/excluir/<?= $c['id'] ?>" class="btn" onclick="return confirm('Tem certeza?')">Excluir</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>