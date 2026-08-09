<?php
/**
 * Arquivo: Views/admin/solicitacoes/listar.php
 * Função: VIEW de gerenciamento de solicitações pelo administrador.
 * 
 * Exibe uma tabela com todas as solicitações, com filtros e paginação.
 * Permite atualizar status e resposta através de um modal.
 * Mobile: visualização em cards.
 */

if (!isset($solicitacoes) || !is_array($solicitacoes)) {
    $solicitacoes = [];
}

$paginaAtual = $paginacao['pagina_atual'] ?? 1;
$totalPaginas = $paginacao['total_paginas'] ?? 1;
$statusAtual = $_GET['status'] ?? '';
$termo = $_GET['termo'] ?? '';
$queryBase = $_GET;
unset($queryBase['pagina']);

$titulo = 'Gerenciar Solicitações';
require __DIR__ . '/../../partials/dashboard_header.php';
?>

<h1>Solicitações dos Representantes</h1>

<!-- Filtros -->
<form method="GET" action="/admin/solicitacoes" class="barra-pesquisa-filtros">
    <input type="text" name="termo" placeholder="Buscar por título..." value="<?= htmlspecialchars($termo) ?>" class="campo-pesquisa">
    <select name="status">
        <option value="">Todos os status</option>
        <option value="pendente" <?= $statusAtual == 'pendente' ? 'selected' : '' ?>>Pendente</option>
        <option value="deferido" <?= $statusAtual == 'deferido' ? 'selected' : '' ?>>Deferido</option>
        <option value="indeferido" <?= $statusAtual == 'indeferido' ? 'selected' : '' ?>>Indeferido</option>
        <option value="em_desenvolvimento" <?= $statusAtual == 'em_desenvolvimento' ? 'selected' : '' ?>>Em Desenvolvimento</option>
        <option value="teste" <?= $statusAtual == 'teste' ? 'selected' : '' ?>>Teste</option>
        <option value="concluido" <?= $statusAtual == 'concluido' ? 'selected' : '' ?>>Concluído</option>
    </select>
    <button type="submit" class="btn-primary">Filtrar</button>
    <a href="/admin/solicitacoes" class="btn btn-limpar">Limpar</a>
</form>

<?php if (empty($solicitacoes)): ?>
    <p class="mensagem-vazia">Nenhuma solicitação encontrada.</p>
<?php else: ?>
    <div class="tabela-responsiva">
        <table>
            <thead>
                <tr>
                    <th>Representante</th>
                    <th>Título</th>
                    <th>Descrição</th>
                    <th>Resposta</th>
                    <th>Status</th>
                    <th>Data</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($solicitacoes as $s): ?>
                <tr>
                    <td data-label="Representante"><?= htmlspecialchars($s['representante_nome']) ?></td>
                    <td data-label="Título"><?= htmlspecialchars($s['titulo']) ?></td>
                    <td data-label="Descrição"><?= nl2br(htmlspecialchars($s['descricao'])) ?></td>
                    <td data-label="Resposta"><?= nl2br(htmlspecialchars($s['resposta'] ?? '')) ?></td>
                    <td data-label="Status" class="status-<?= $s['status'] ?>">
                        <?= ucfirst(str_replace('_', ' ', $s['status'])) ?>
                    </td>
                    <td data-label="Data"><?= date('d/m/Y H:i', strtotime($s['criado_em'])) ?></td>
                    <td data-label="Ações">
                        <button type="button" class="btn btn-editar-solicitacao"
                                data-id="<?= $s['id'] ?>"
                                data-titulo="<?= htmlspecialchars($s['titulo']) ?>"
                                data-status="<?= $s['status'] ?>"
                                data-resposta="<?= htmlspecialchars($s['resposta'] ?? '') ?>">
                            Editar
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    <?php if ($totalPaginas > 1): ?>
    <div class="paginacao">
        <?php if ($paginaAtual > 1): ?>
            <a href="/admin/solicitacoes?<?= http_build_query(array_merge($queryBase, ['pagina' => $paginaAtual - 1])) ?>">&laquo; Anterior</a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
            <?php if ($i == $paginaAtual): ?>
                <span class="pagina-atual"><?= $i ?></span>
            <?php else: ?>
                <a href="/admin/solicitacoes?<?= http_build_query(array_merge($queryBase, ['pagina' => $i])) ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        <?php if ($paginaAtual < $totalPaginas): ?>
            <a href="/admin/solicitacoes?<?= http_build_query(array_merge($queryBase, ['pagina' => $paginaAtual + 1])) ?>">Próximo &raquo;</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
<?php endif; ?>

<!-- Modal de edição de status e resposta -->
<div id="modalEditarSolicitacao" class="modal-overlay" style="display:none;">
    <div class="modal-content">
        <span class="modal-close" id="modalEditarSolicitacaoClose">&times;</span>
        <h2>Editar Solicitação</h2>
        <form id="formEditarSolicitacao" method="POST" action="/admin/solicitacoes/atualizar">
            <input type="hidden" name="id" id="edit-solicitacao-id">
            <div class="input-group">
                <label>Título:</label>
                <input type="text" id="edit-solicitacao-titulo" readonly>
            </div>
            <div class="input-group">
                <label for="edit-solicitacao-status">Status:</label>
                <select name="status" id="edit-solicitacao-status">
                    <option value="pendente">Pendente</option>
                    <option value="deferido">Deferido</option>
                    <option value="indeferido">Indeferido</option>
                    <option value="em_desenvolvimento">Em Desenvolvimento</option>
                    <option value="teste">Teste</option>
                    <option value="concluido">Concluído</option>
                </select>
            </div>
            <div class="input-group">
                <label for="edit-solicitacao-resposta">Resposta:</label>
                <textarea name="resposta" id="edit-solicitacao-resposta" rows="4"></textarea>
            </div>
            <button type="submit" class="btn-primary">Salvar</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Abrir modal de edição ao clicar em "Editar"
    document.querySelectorAll('.btn-editar-solicitacao').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit-solicitacao-id').value = this.dataset.id;
            document.getElementById('edit-solicitacao-titulo').value = this.dataset.titulo;
            document.getElementById('edit-solicitacao-status').value = this.dataset.status;
            document.getElementById('edit-solicitacao-resposta').value = this.dataset.resposta;
            document.getElementById('modalEditarSolicitacao').style.display = 'flex';
        });
    });

    // Fechar modal
    document.getElementById('modalEditarSolicitacaoClose').addEventListener('click', function() {
        document.getElementById('modalEditarSolicitacao').style.display = 'none';
    });

    // Fechar ao clicar fora do modal
    window.addEventListener('click', function(e) {
        if (e.target === document.getElementById('modalEditarSolicitacao')) {
            document.getElementById('modalEditarSolicitacao').style.display = 'none';
        }
    });
});
</script>

<?php require __DIR__ . '/../../partials/dashboard_footer.php'; ?>