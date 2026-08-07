<?php
/**
 * Arquivo: Views/admin/licencas/listar.php
 * Função: VIEW de listagem de licenças (painel admin).
 * 
 * Exibe uma tabela com todas as licenças do sistema, permitindo ao
 * administrador:
 *   - Visualizar cliente, CPF/CNPJ, representante responsável,
 *     chave (parcial), data de expiração, valor total e status.
 *   - Renovar licenças manualmente.
 *   - Gerar token offline para ativação manual pelo cliente.
 *   - Editar o cadastro do cliente (inclusive módulos contratados).
 *   - Registrar pagamento do cliente.
 * 
 * Alertas visuais:
 *   - ⚠️ a partir do dia 28 (licença próxima do vencimento).
 *   - Status "Expirada" em vermelho quando a data já passou.
 */

// Garante que a variável $licencas seja sempre um array
if (!isset($licencas) || !is_array($licencas)) {
    $licencas = [];
}

// Mensagens temporárias de token (exibidas uma vez)
$tokenGerado = $_SESSION['token_gerado'] ?? null;
$erroToken = $_SESSION['erro_token'] ?? null;
unset($_SESSION['token_gerado'], $_SESSION['erro_token']);

// Título da página
$titulo = 'Gerenciar Licenças (Admin)';

// Inclui o cabeçalho comum (HTML, CSS, favicon)
require __DIR__ . '/../../partials/dashboard_header.php';
?>

<h1>Licenças (Admin)</h1>
<p>
    <a href="/admin/representantes" class="btn">Voltar para Representantes</a>
</p>

<!-- Exibe token gerado (se houver) -->
<?php if ($tokenGerado): ?>
    <div class="mensagem sucesso">
        <strong>Token Offline Gerado:</strong><br>
        <textarea rows="3" readonly><?= htmlspecialchars($tokenGerado) ?></textarea>
    </div>
<?php endif; ?>

<!-- Exibe erro de token (se houver) -->
<?php if ($erroToken): ?>
    <div class="mensagem erro">
        <?= htmlspecialchars($erroToken) ?>
    </div>
<?php endif; ?>

<!-- Tabela de licenças -->
<table>
    <tr>
        <th>Cliente</th>
        <th>CPF/CNPJ</th>
        <th>Representante</th>
        <th>Chave</th>
        <th>Expiração</th>
        <th>Valor Total</th>
        <th>Status</th>
        <th>Ações</th>
    </tr>
    <?php foreach ($licencas as $l):
        // Cálculo para alerta de vencimento
        $dataExp = new DateTime($l['data_expiracao']);
        $hoje = new DateTime();
        $expirada = $dataExp < $hoje;
        $diaAtual = (int)$hoje->format('d');
        $alerta = !$expirada && $diaAtual >= 28; // alerta a partir do dia 28
    ?>
    <tr>
        <td><?= htmlspecialchars($l['cliente_nome']) ?></td>
        <td><?= htmlspecialchars($l['cpf_cnpj']) ?></td>
        <td><?= htmlspecialchars($l['representante_nome']) ?></td>
        <td><code><?= substr($l['chave'], 0, 16) ?>...</code></td>
        <td class="<?= $expirada ? 'expirada' : ($alerta ? 'alerta' : '') ?>">
            <?= $dataExp->format('d/m/Y') ?>
            <?php if ($alerta): ?> ⚠️<?php endif; ?>
        </td>
        <td>R$ <?= number_format($l['valor_total_atual'] ?? 0, 2, ',', '.') ?></td>
        <td class="<?= $l['ativa'] && !$expirada ? 'ativa' : 'expirada' ?>">
            <?= $l['ativa'] && !$expirada ? 'Ativa' : 'Expirada/Inativa' ?>
        </td>
        <td>
            <a href="/admin/licencas/renovar/<?= $l['cliente_id'] ?>" class="btn" onclick="return confirm('Renovar licença?')">Renovar</a>
            <a href="/admin/licencas/gerar-token/<?= $l['cliente_id'] ?>" class="btn" onclick="return confirm('Gerar token offline?')">Gerar Token</a>
            <a href="/admin/clientes/editar/<?= $l['cliente_id'] ?>" class="btn">Editar Cliente</a>
            <button type="button" class="btn btn-pagar"
                    data-cliente-id="<?= $l['cliente_id'] ?>"
                    data-nome="<?= htmlspecialchars($l['cliente_nome']) ?>">
                Pagar
            </button>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<!-- Modal de pagamento -->
<div id="modalPagamento" class="modal-overlay" style="display:none;">
    <div class="modal-content">
        <span class="modal-close" id="modalPagamentoClose">&times;</span>
        <h2>Registrar Pagamento</h2>
        <form method="POST" action="/admin/licencas/pagar">
            <input type="hidden" name="cliente_id" id="pg-cliente-id">
            <div class="input-group">
                <label>Cliente: <span id="pg-cliente-nome" style="font-weight:bold;"></span></label>
            </div>
            <div class="input-group">
                <label for="pg-valor">Valor (R$)</label>
                <input type="number" step="0.01" name="valor" id="pg-valor" required>
            </div>
            <div class="input-group">
                <label for="pg-data">Data do Pagamento</label>
                <input type="date" name="data_pagamento" id="pg-data" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="input-group">
                <label for="pg-mes">Mês de Referência</label>
                <input type="month" name="mes_referencia" id="pg-mes" value="<?= date('Y-m') ?>" required>
            </div>
            <div class="input-group">
                <label for="pg-obs">Observação</label>
                <input type="text" name="observacao" id="pg-obs">
            </div>
            <button type="submit" class="btn-primary">Registrar Pagamento</button>
        </form>
    </div>
</div>

<script>
// Abrir modal de pagamento ao clicar em "Pagar"
document.querySelectorAll('.btn-pagar').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const clienteId = this.dataset.clienteId;
        const nome = this.dataset.nome;
        document.getElementById('pg-cliente-id').value = clienteId;
        document.getElementById('pg-cliente-nome').textContent = nome;
        document.getElementById('modalPagamento').style.display = 'flex';
    });
});

// Fechar modal
document.getElementById('modalPagamentoClose').addEventListener('click', function() {
    document.getElementById('modalPagamento').style.display = 'none';
});
</script>

<?php require __DIR__ . '/../../partials/dashboard_footer.php'; ?>