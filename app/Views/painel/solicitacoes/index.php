<?php
/**
 * Arquivo: Views/painel/solicitacoes/index.php
 * Função: VIEW de solicitações do representante.
 * 
 * Organizado em abas:
 *   - Aba 1: Nova Solicitação (formulário para envio)
 *   - Aba 2: Histórico (tabela com filtros, paginação, ações de editar e visualizar)
 *   - Mobile: visualização em cards.
 */

if (!isset($solicitacoes) || !is_array($solicitacoes)) {
    $solicitacoes = [];
}

$titulo = 'Solicitações';
require __DIR__ . '/../../partials/dashboard_header.php';
$sucesso = $sucesso ?? null;

// Parâmetros atuais dos filtros (para manter na paginação)
$termoAtual = $_GET['termo'] ?? '';
$statusAtual = $_GET['status'] ?? '';
?>

<h1>Minhas Solicitações</h1>

<?php if ($sucesso): ?>
    <div class="mensagem-sucesso"><?= htmlspecialchars($sucesso) ?></div>
<?php endif; ?>

<!-- ==================== ABAS ==================== -->
<div class="tabs-container">
    <div class="tabs-nav">
        <button type="button" class="tab-btn active" data-tab="tab-nova">
            <i class="fas fa-plus-circle"></i> Nova Solicitação
        </button>
        <button type="button" class="tab-btn" data-tab="tab-historico">
            <i class="fas fa-history"></i> Histórico
        </button>
    </div>

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
            <!-- Filtros -->
            <form method="GET" action="/painel/solicitacoes" class="barra-pesquisa-filtros">
                <input type="text" name="termo" placeholder="Buscar por palavra-chave" value="<?= htmlspecialchars($termoAtual) ?>" class="campo-pesquisa">
                <select name="status">
                    <option value="">Todos os status</option>
                    <option value="pendente" <?= $statusAtual == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                    <option value="deferido" <?= $statusAtual == 'deferido' ? 'selected' : '' ?>>Deferido</option>
                    <option value="indeferido" <?= $statusAtual == 'indeferido' ? 'selected' : '' ?>>Indeferido</option>
                    <option value="em_desenvolvimento" <?= $statusAtual == 'em_desenvolvimento' ? 'selected' : '' ?>>Em Desenvolvimento</option>
                    <option value="teste" <?= $statusAtual == 'teste' ? 'selected' : '' ?>>Teste</option>
                    <option value="concluido" <?= $statusAtual == 'concluido' ? 'selected' : '' ?>>Concluído</option>
                </select>
                <!-- Wrapper para os botões ficarem lado a lado -->
                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="btn-primary">Filtrar</button>
                    <a href="/painel/solicitacoes" class="btn btn-limpar">Limpar</a>
                </div>
            </form>

            <?php if (empty($solicitacoes)): ?>
                <p class="mensagem-vazia">Nenhuma solicitação encontrada.</p>
            <?php else: ?>
                <div class="tabela-responsiva">
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
                                <td data-label="Título"><?= htmlspecialchars($s['titulo']) ?></td>
                                <td data-label="Status" class="status-<?= $s['status'] ?>">
                                    <?= ucfirst(str_replace('_', ' ', $s['status'])) ?>
                                </td>
                                <td data-label="Data"><?= date('d/m/Y', strtotime($s['criado_em'])) ?></td>
                                <td data-label="Última Atualização"><?= date('d/m/Y H:i', strtotime($s['atualizado_em'])) ?></td>
                                <td data-label="Ações">
                                    <?php if ($s['status'] === 'pendente'): ?>
                                        <button type="button" class="btn-editar" data-id="<?= $s['id'] ?>">Editar</button>
                                    <?php endif; ?>
                                    <button type="button" class="btn-ver" data-id="<?= $s['id'] ?>">Ver</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginação -->
                <?php
                $paginaAtual = $paginacao['pagina_atual'] ?? 1;
                $totalPaginas = $paginacao['total_paginas'] ?? 1;
                if ($totalPaginas > 1):
                    $queryParams = $_GET;
                    unset($queryParams['pagina']);
                ?>
                <div class="paginacao">
                    <?php if ($paginaAtual > 1): ?>
                        <a href="/painel/solicitacoes?<?= http_build_query(array_merge($queryParams, ['pagina' => $paginaAtual - 1])) ?>">&laquo; Anterior</a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                        <?php if ($i == $paginaAtual): ?>
                            <span class="pagina-atual"><?= $i ?></span>
                        <?php else: ?>
                            <a href="/painel/solicitacoes?<?= http_build_query(array_merge($queryParams, ['pagina' => $i])) ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <?php if ($paginaAtual < $totalPaginas): ?>
                        <a href="/painel/solicitacoes?<?= http_build_query(array_merge($queryParams, ['pagina' => $paginaAtual + 1])) ?>">Próximo &raquo;</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ==================== MODAL DE EDIÇÃO (mantido igual) ==================== -->
<div id="modalEditar" class="modal-overlay">
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
        <div id="edit-msg" class="modal-msg"></div>
    </div>
</div>

<!-- Modal de Visualização -->
<div id="modalVer" class="modal-overlay">
    <div class="modal-content">
        <span class="modal-close" id="modalVerClose">&times;</span>
        <h2>Detalhes da Solicitação</h2>
        <div id="ver-conteudo">
            <p><strong>Título:</strong> <span id="ver-titulo"></span></p>
            <p><strong>Status:</strong> <span id="ver-status"></span></p>
            <p><strong>Data de criação:</strong> <span id="ver-data"></span></p>
            <p><strong>Descrição:</strong></p>
            <div id="ver-descricao" style="white-space: pre-wrap; background:#f8fafc; padding:10px; border-radius:6px; margin-bottom:12px;"></div>
            <p><strong>Resposta do Administrador:</strong></p>
            <div id="ver-resposta" style="white-space: pre-wrap; background:#f8fafc; padding:10px; border-radius:6px; color:#1a2a3a;"></div>
        </div>
    </div>
</div>

<!-- Scripts (mantidos) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-editar').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            fetch('/painel/solicitacoes/editar/' + id)
                .then(res => res.json())
                .then(data => {
                    if (data.erro) { alert(data.erro); return; }
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
        fetch('/painel/solicitacoes/atualizar', {
            method: 'POST',
            body: new FormData(this)
        }).then(() => { window.location.href = '/painel/solicitacoes?sucesso=1'; });
    });
    document.querySelectorAll('.btn-ver').forEach(btn => {
        btn.addEventListener('click', function() {
            fetch('/painel/solicitacoes/ver/' + this.dataset.id)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('ver-titulo').textContent = data.titulo;
                    document.getElementById('ver-status').textContent = ucfirst(data.status.replace(/_/g, ' '));
                    document.getElementById('ver-data').textContent = new Date(data.criado_em).toLocaleDateString('pt-BR');
                    document.getElementById('ver-descricao').textContent = data.descricao;
                    document.getElementById('ver-resposta').textContent = data.resposta || 'Aguardando resposta...';
                    document.getElementById('modalVer').style.display = 'flex';
                });
        });
    });
    document.getElementById('modalVerClose').addEventListener('click', function() {
        document.getElementById('modalVer').style.display = 'none';
    });
    function ucfirst(str) { return str.charAt(0).toUpperCase() + str.slice(1); }
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            tabBtns.forEach(b => b.classList.remove('active'));
            tabPanes.forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            document.getElementById(this.getAttribute('data-tab')).classList.add('active');
        });
    });
});
</script>

<?php require __DIR__ . '/../../partials/dashboard_footer.php'; ?>