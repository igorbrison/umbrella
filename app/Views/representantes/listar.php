<?php
/**
 * Arquivo: listar.php
 * Função: VIEW da lista de representantes.
 * 
 * Características:
 *   - Exibe uma tabela com todos os representantes cadastrados.
 *   - Cabeçalhos clicáveis permitem ordenação por coluna (ordem crescente/descendente).
 *   - Links para ações: Criar, Editar, Ativar/Desativar, Excluir.
 *   - Apenas o Administrador (dono da empresa) tem acesso a esta tela.
 */

// Garante que $representantes seja sempre um array, evitando avisos de análise estática
if (!isset($representantes) || !is_array($representantes)) {
    $representantes = [];
}

// Captura a coluna e direção atuais da ordenação, enviadas pelo Controller
$colAtual = $ordenacaoAtual['coluna'] ?? 'id';
$dirAtual = $ordenacaoAtual['direcao'] ?? 'asc';

/**
 * Função auxiliar: gera a URL de ordenação para cada cabeçalho da tabela.
 * - Se a coluna clicada já está ativa, alterna a direção (asc <-> desc).
 * - Caso contrário, inicia sempre com ascendente.
 */
function urlOrdenacao(string $coluna, string $colAtual, string $dirAtual): string {
    $novaDirecao = ($coluna === $colAtual && $dirAtual === 'asc') ? 'desc' : 'asc';
    return "?ordem=$coluna&direcao=$novaDirecao";
}

/**
 * Função auxiliar: exibe uma seta indicativa (▲ ou ▼) ao lado da coluna atualmente ordenada.
 */
function seta(string $coluna, string $colAtual, string $dirAtual): string {
    if ($coluna !== $colAtual) return '';
    return $dirAtual === 'asc' ? ' ▲' : ' ▼';
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Representantes</title>
    <!-- Estilos simples para a tabela e botões -->
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; }
        th { background: #f5f5f5; }
        th a { text-decoration: none; color: inherit; display: block; }
        th a:hover { text-decoration: underline; }
        .btn { padding: 5px 10px; text-decoration: none; background: #eee; border:1px solid #aaa; border-radius:3px; }
    </style>
</head>
<body>
    <h1>Representantes</h1>
    <!-- Botão para cadastrar novo representante -->
    <a href="/admin/representantes/criar" class="btn">Novo Representante</a>

    <!-- Tabela de representantes com ordenação clicável -->
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
                <!-- Link para Editar -->
                <a href="/admin/representantes/editar/<?= $r['id'] ?>" class="btn">Editar</a>
                <!-- Link para Ativar/Desativar (toggle) -->
                <a href="/admin/representantes/status/<?= $r['id'] ?>" class="btn"><?= $r['ativo'] ? 'Desativar' : 'Ativar' ?></a>
                <!-- Link para Excluir (com confirmação) -->
                <a href="/admin/representantes/excluir/<?= $r['id'] ?>" class="btn" onclick="return confirm('Tem certeza?')">Excluir</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>