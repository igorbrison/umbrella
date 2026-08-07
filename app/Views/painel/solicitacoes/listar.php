<?php
/**
 * Arquivo: Views/admin/solicitacoes/listar.php
 * Função: VIEW de gerenciamento de solicitações pelo administrador.
 * 
 * Exibe uma tabela com todas as solicitações enviadas pelos representantes,
 * permitindo ao administrador:
 *   - Visualizar o representante, título, descrição e status atual.
 *   - Alterar o status de cada solicitação através de um seletor.
 * 
 * Status disponíveis:
 *   - Pendente
 *   - Deferido
 *   - Indeferido
 *   - Em Desenvolvimento
 *   - Teste
 *   - Concluído
 * 
 * Uso dos parciais:
 *   - dashboard_header.php : barra superior, menu lateral e abertura do main-content.
 *   - dashboard_footer.php : fechamento das tags abertas pelo header.
 */

// Inicializa variáveis para evitar avisos de análise
if (!isset($solicitacoes) || !is_array($solicitacoes)) {
    $solicitacoes = [];
}

// Título da página
$titulo = 'Gerenciar Solicitações';

// Inclui o cabeçalho do painel (barra superior, menu lateral, abertura do main-content)
require __DIR__ . '/../../partials/dashboard_header.php';
?>

<h1>Solicitações dos Representantes</h1>

<!-- Tabela de solicitações -->
<table>
    <thead>
        <tr>
            <th>Representante</th>
            <th>Título</th>
            <th>Descrição</th>
            <th>Status</th>
            <th>Ação</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($solicitacoes as $s): ?>
        <tr>
            <td><?= htmlspecialchars($s['representante_nome']) ?></td>
            <td><?= htmlspecialchars($s['titulo']) ?></td>
            <td><?= nl2br(htmlspecialchars($s['descricao'])) ?></td>
            <td class="status-<?= $s['status'] ?>"><?= ucfirst(str_replace('_', ' ', $s['status'])) ?></td>
            <td>
                <form method="POST" action="/admin/solicitacoes/atualizar" class="status-actions">
                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                    <select name="status">
                        <option value="pendente" <?= $s['status'] == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                        <option value="deferido" <?= $s['status'] == 'deferido' ? 'selected' : '' ?>>Deferido</option>
                        <option value="indeferido" <?= $s['status'] == 'indeferido' ? 'selected' : '' ?>>Indeferido</option>
                        <option value="em_desenvolvimento" <?= $s['status'] == 'em_desenvolvimento' ? 'selected' : '' ?>>Em Desenvolvimento</option>
                        <option value="teste" <?= $s['status'] == 'teste' ? 'selected' : '' ?>>Teste</option>
                        <option value="concluido" <?= $s['status'] == 'concluido' ? 'selected' : '' ?>>Concluído</option>
                    </select>
                    <button type="submit">Atualizar</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php
// Inclui o rodapé do painel (fecha main-content, div wrapper, body e html)
require __DIR__ . '/../../partials/dashboard_footer.php';
?>