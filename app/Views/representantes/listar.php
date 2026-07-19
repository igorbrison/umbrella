<?php
/**
 * Arquivo: Views/representantes/listar.php
 * Função: VIEW de LISTAGEM de representantes.
 * 
 * Esta página exibe uma tabela com todos os representantes cadastrados,
 * permitindo:
 *   - Ordenação dinâmica clicando nos cabeçalhos das colunas.
 *   - Ações rápidas (Editar, Ativar/Desativar, Excluir).
 *   - Link para criar um novo representante.
 * 
 * Dados recebidos do Controller (RepresentanteController@index):
 *   - $representantes: array com os registros do banco.
 *   - $ordenacaoAtual: array com 'coluna' e 'direcao' atuais.
 * 
 * Observação: Esta view NÃO realiza operações de banco, apenas exibe dados.
 */

// 1. SEGURANÇA E FALLBACK PARA $representantes
// --------------------------------------------------------------
// Garante que $representantes seja sempre um array, mesmo se o controller
// não o definiu ou se for nulo. Isso evita erros no foreach mais adiante.
if (!isset($representantes) || !is_array($representantes)) {
    $representantes = [];
}

// 2. EXTRAÇÃO DOS PARÂMETROS DE ORDENAÇÃO ATUAIS
// --------------------------------------------------------------
// $ordenacaoAtual é passada pelo controller com a estrutura:
//   ['coluna' => 'nome_razao', 'direcao' => 'asc']
// Usamos valores padrão caso não esteja definida.
$colAtual = $ordenacaoAtual['coluna'] ?? 'id';
$dirAtual = $ordenacaoAtual['direcao'] ?? 'asc';

// 3. FUNÇÕES AUXILIARES PARA A ORDENAÇÃO
// --------------------------------------------------------------
// Elas são definidas no escopo da view para facilitar a geração dos links.

/**
 * Gera a URL para ordenar por uma determinada coluna.
 * 
 * @param string $coluna   A coluna pela qual se deseja ordenar.
 * @param string $colAtual Coluna atualmente em ordenação.
 * @param string $dirAtual Direção atual ('asc' ou 'desc').
 * @return string URL com os parâmetros 'ordem' e 'direcao'.
 * 
 * Lógica: Se a coluna clicada já é a atual, inverte a direção (asc <-> desc).
 *         Caso contrário, inicia com 'asc' (ordem crescente).
 */
function urlOrdenacao(string $coluna, string $colAtual, string $dirAtual): string {
    // Determina a nova direção:
    // - Se a coluna é a mesma e a direção atual é 'asc', vira 'desc'.
    // - Caso contrário, vira 'asc' (primeiro clique em uma coluna nova).
    $novaDirecao = ($coluna === $colAtual && $dirAtual === 'asc') ? 'desc' : 'asc';
    return "?ordem=$coluna&direcao=$novaDirecao";
}

/**
 * Retorna um indicador visual (seta) para mostrar a ordenação atual.
 * 
 * @param string $coluna   Coluna que está sendo avaliada.
 * @param string $colAtual Coluna atualmente ordenada.
 * @param string $dirAtual Direção atual ('asc' ou 'desc').
 * @return string Seta para cima (▲) ou para baixo (▼) ou vazio.
 * 
 * Exemplo: Se a tabela está ordenada por 'nome' ascendente, a coluna 'nome'
 *          exibirá ' ▲' ao lado do título.
 */
function seta(string $coluna, string $colAtual, string $dirAtual): string {
    // Se não for a coluna ativa, não exibe nada.
    if ($coluna !== $colAtual) return '';
    // Retorna a seta correspondente à direção atual.
    return $dirAtual === 'asc' ? ' ▲' : ' ▼';
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Representantes</title>
    <style>
        /* Estilos básicos para a tabela e botões */
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; }
        th { background: #f5f5f5; }
        /* Os cabeçalhos são links clicáveis para ordenação */
        th a { text-decoration: none; color: inherit; display: block; }
        th a:hover { text-decoration: underline; }
        .btn { padding: 5px 10px; text-decoration: none; background: #eee; border:1px solid #aaa; border-radius:3px; }
    </style>
</head>
<body>
    <h1>Representantes</h1>

    <!-- Link para criar um novo representante (chama o método 'criar' do controller) -->
    <a href="/representantes/criar" class="btn">Novo Representante</a>

    <!-- TABELA DE LISTAGEM -->
    <table>
        <!-- Cabeçalho com links de ordenação -->
        <tr>
            <th>
                <!-- Cada <th> contém um link que, ao ser clicado, recarrega a página 
                     com os parâmetros ?ordem=...&direcao=... -->
                <a href="<?= urlOrdenacao('id', $colAtual, $dirAtual) ?>">
                    ID<?= seta('id', $colAtual, $dirAtual) ?>
                </a>
            </th>
            <th>
                <a href="<?= urlOrdenacao('nome_razao', $colAtual, $dirAtual) ?>">
                    Razão Social<?= seta('nome_razao', $colAtual, $dirAtual) ?>
                </a>
            </th>
            <th>
                <a href="<?= urlOrdenacao('nome_fantasia', $colAtual, $dirAtual) ?>">
                    Nome Fantasia<?= seta('nome_fantasia', $colAtual, $dirAtual) ?>
                </a>
            </th>
            <th>
                <a href="<?= urlOrdenacao('cnpj', $colAtual, $dirAtual) ?>">
                    CNPJ<?= seta('cnpj', $colAtual, $dirAtual) ?>
                </a>
            </th>
            <th>
                <a href="<?= urlOrdenacao('email', $colAtual, $dirAtual) ?>">
                    Email<?= seta('email', $colAtual, $dirAtual) ?>
                </a>
            </th>
            <th>
                <a href="<?= urlOrdenacao('comissao_percentual', $colAtual, $dirAtual) ?>">
                    Comissão (%)<?= seta('comissao_percentual', $colAtual, $dirAtual) ?>
                </a>
            </th>
            <th>
                <a href="<?= urlOrdenacao('ativo', $colAtual, $dirAtual) ?>">
                    Status<?= seta('ativo', $colAtual, $dirAtual) ?>
                </a>
            </th>
            <th>Ações</th>
        </tr>

        <!-- LOOP PARA EXIBIR CADA REGISTRO -->
        <?php foreach ($representantes as $r): ?>
        <tr>
            <!-- Dados do representante -->
            <td><?= $r['id'] ?></td>
            <td><?= htmlspecialchars($r['nome_razao']) ?></td>
            <td><?= htmlspecialchars($r['nome_fantasia']) ?></td>
            <td><?= htmlspecialchars($r['cnpj']) ?></td>
            <td><?= htmlspecialchars($r['email']) ?></td>
            <td>
                <?php 
                    // Formata a comissão: se for null, exibe hífen; senão, formata com duas casas decimais
                    echo $r['comissao_percentual'] !== null 
                        ? number_format($r['comissao_percentual'], 2, ',', '.') . '%' 
                        : '-'; 
                ?>
            </td>
            <td><?= $r['ativo'] ? 'Ativo' : 'Inativo' ?></td>

            <!-- Coluna de ações (botões) -->
            <td>
                <!-- Editar: leva para o formulário preenchido -->
                <a href="/representantes/editar/<?= $r['id'] ?>" class="btn">Editar</a>

                <!-- Alternar status: chama a rota 'status' que inverte o campo ativo -->
                <a href="/representantes/status/<?= $r['id'] ?>" class="btn">
                    <?= $r['ativo'] ? 'Desativar' : 'Ativar' ?>
                </a>

                <!-- Excluir: ação destrutiva. Inclui confirmação em JS -->
                <a href="/representantes/excluir/<?= $r['id'] ?>" class="btn" 
                   onclick="return confirm('Tem certeza que deseja excluir este representante?')">
                    Excluir
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>