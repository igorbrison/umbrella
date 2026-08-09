<?php
/**
 * Arquivo: Views/admin/solicitacoes/listar.php
 * Função: VIEW de gerenciamento de solicitações pelo administrador.
 * 
 * Exibe uma tabela com todas as solicitações, permitindo alterar status e resposta.
 * Mobile: visualização em cards.
 */

if (!isset($solicitacoes) || !is_array($solicitacoes)) {
    $solicitacoes = [];
}

$titulo = 'Gerenciar Solicitações';
require __DIR__ . '/../../partials/dashboard_header.php';
?>

<h1>Solicitações dos Representantes</h1>

<div class="tabela-responsiva">
    <table>
        <thead>
            <tr>
                <th>Representante</th>
                <th>Título</th>
                <th>Descrição</th>
                <th>Resposta</th>
                <th>Status</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($solicitacoes as $s): ?>
            <tr>
                <td data-label="Representante"><?= htmlspecialchars($s['representante_nome']) ?></td>
                <td data-label="Título"><?= htmlspecialchars($s['titulo']) ?></td>
                <td data-label="Descrição"><?= nl2br(htmlspecialchars($s['descricao'])) ?></td>
                <td data-label="Resposta"><?= nl2br(htmlspecialchars($s['resposta'] ?? 'Pendente')) ?></td>
                <td data-label="Status"><?= ucfirst(str_replace('_', ' ', $s['status'])) ?></td>
                <td data-label="Ação">
                    <form method="POST" action="/admin/solicitacoes/atualizar">
                        <input type="hidden" name="id" value="<?= $s['id'] ?>">
                        <select name="status" style="margin-bottom:4px;">
                            <option value="pendente" <?= $s['status'] == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                            <option value="deferido" <?= $s['status'] == 'deferido' ? 'selected' : '' ?>>Deferido</option>
                            <option value="indeferido" <?= $s['status'] == 'indeferido' ? 'selected' : '' ?>>Indeferido</option>
                            <option value="em_desenvolvimento" <?= $s['status'] == 'em_desenvolvimento' ? 'selected' : '' ?>>Em Desenvolvimento</option>
                            <option value="teste" <?= $s['status'] == 'teste' ? 'selected' : '' ?>>Teste</option>
                            <option value="concluido" <?= $s['status'] == 'concluido' ? 'selected' : '' ?>>Concluído</option>
                        </select>
                        <textarea name="resposta" rows="2" placeholder="Resposta..." style="width:100%; margin-bottom:4px;"><?= htmlspecialchars($s['resposta'] ?? '') ?></textarea>
                        <button type="submit" class="btn-primary" style="padding:4px 12px; font-size:13px;">Atualizar</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../../partials/dashboard_footer.php'; ?>