<?php
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
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Módulos</title>
    <link rel="stylesheet" href="/css/style.css">
    <!--
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; }
        th { background: #f5f5f5; }
        th a { text-decoration: none; color: inherit; display: block; }
        th a:hover { text-decoration: underline; }
        .btn { padding: 5px 10px; text-decoration: none; background: #eee; border:1px solid #aaa; border-radius:3px; margin-right: 5px; }
    </style>
-->
</head>
<body>
    <h1>Módulos</h1>
    <p>
        <a href="/admin/modulos/criar" class="btn">Novo Módulo</a>
        <a href="/admin/representantes" class="btn">Voltar para Representantes</a>
    </p>
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
            <td><?= number_format($m['valor'], 2, ',', '.') ?></td>
            <td><?= $m['ativo'] ? 'Sim' : 'Não' ?></td>
            <td>
                <a href="/admin/modulos/editar/<?= $m['id'] ?>" class="btn">Editar</a>
                <a href="/admin/modulos/excluir/<?= $m['id'] ?>" class="btn" onclick="return confirm('Tem certeza que deseja excluir este módulo?')">Excluir</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>