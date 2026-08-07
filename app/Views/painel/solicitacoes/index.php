<?php
/**
 * Arquivo: Views/painel/solicitacoes/index.php
 * Função: VIEW de solicitações do representante.
 * 
 * Organizado em abas:
 *   - Aba 1: Nova Solicitação (formulário para envio)
 *   - Aba 2: Histórico (tabela com todas as solicitações)
 */

if (!isset($solicitacoes) || !is_array($solicitacoes)) {
    $solicitacoes = [];
}

$titulo = 'Solicitações';
require __DIR__ . '/../../partials/dashboard_header.php';
$sucesso = $sucesso ?? null;
?>

<h1>Minhas Solicitações</h1>

<?php if ($sucesso): ?>
    <div class="mensagem-sucesso"><?= htmlspecialchars($sucesso) ?></div>
<?php endif; ?>

<!-- ==================== ABAS ==================== -->
<div class="tabs-container">
    <!-- Navegação das abas -->
    <div class="tabs-nav">
        <button type="button" class="tab-btn active" data-tab="tab-nova">
            <i class="fas fa-plus-circle"></i> Nova Solicitação
        </button>
        <button type="button" class="tab-btn" data-tab="tab-historico">
            <i class="fas fa-history"></i> Histórico
        </button>
    </div>

    <!-- Conteúdo das abas -->
    <div class="tab-content">
        <!-- ===================== ABA 1: NOVA SOLICITAÇÃO ===================== -->
        <div id="tab-nova" class="tab-pane active">
            <form method="POST" action="/painel/solicitacoes/enviar" class="solicitacao-form">
                <div class="form-row">
                    <div class="form-col">
                        <label>Título <span class="obrigatorio">*</span>:
                            <input type="text" name="titulo" placeholder="Digite o título da solicitação" required>
                        </label>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <label>Descrição <span class="obrigatorio">*</span>:
                            <textarea name="descricao" rows="5" placeholder="Descreva detalhadamente sua solicitação..." required></textarea>
                        </label>
                    </div>
                </div>
                <button type="submit" class="btn-enviar">Enviar</button>
            </form>
        </div>

        <!-- ===================== ABA 2: HISTÓRICO ===================== -->
        <div id="tab-historico" class="tab-pane">
            <?php if (empty($solicitacoes)): ?>
                <p style="color:#6c7a8a; text-align:center; padding:20px 0;">Nenhuma solicitação enviada até o momento.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th>Última Atualização</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($solicitacoes as $s): ?>
                        <tr>
                            <td><?= htmlspecialchars($s['titulo']) ?></td>
                            <td class="status-<?= $s['status'] ?>"><?= ucfirst(str_replace('_', ' ', $s['status'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($s['criado_em'])) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($s['atualizado_em'])) ?></td>
                            <td>
                                <?php if ($s['status'] === 'pendente'): ?>
                                    <button type="button" class="btn-editar" data-id="<?= $s['id'] ?>">Editar</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ==================== MODAL DE EDIÇÃO ==================== -->
<div id="modalEditar" class="modal-overlay" style="display:none;">
    <div class="modal-content">
        <span class="modal-close" id="modalEditarClose">&times;</span>
        <h2>Editar Solicitação</h2>
        <form id="formEditarSolicitacao">
            <input type="hidden" name="id" id="edit-id">
            <div class="input-group">
                <label for="edit-titulo">Título</label>
                <input type="text" id="edit-titulo" name="titulo" required>
            </div>
            <div class="input-group">
                <label for="edit-descricao">Descrição</label>
                <textarea id="edit-descricao" name="descricao" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn-primary">Salvar</button>
        </form>
        <div id="edit-msg" style="margin-top:10px;"></div>
    </div>
</div>

<!-- ==================== JAVASCRIPT ==================== -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Modal de edição ---
    document.querySelectorAll('.btn-editar').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            fetch('/painel/solicitacoes/editar/' + id)
                .then(res => res.json())
                .then(data => {
                    if (data.erro) {
                        alert(data.erro);
                        return;
                    }
                    document.getElementById('edit-id').value = data.id;
                    document.getElementById('edit-titulo').value = data.titulo;
                    document.getElementById('edit-descricao').value = data.descricao;
                    document.getElementById('modalEditar').style.display = 'flex';
                });
        });
    });

    document.getElementById('modalEditarClose').addEventListener('click', function() {
        document.getElementById('modalEditar').style.display = 'none';
    });

    document.getElementById('formEditarSolicitacao').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('/painel/solicitacoes/atualizar', {
            method: 'POST',
            body: formData
        })
        .then(res => res.text())
        .then(() => {
            window.location.href = '/painel/solicitacoes?sucesso=1';
        });
    });
});
</script>

<?php require __DIR__ . '/../../partials/dashboard_footer.php'; ?>