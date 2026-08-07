<?php
/**
 * Arquivo: Views/painel/solicitacoes/index.php
 * Função: VIEW de solicitações do representante.
 * 
 * Exibe o formulário para envio de novas solicitações e o histórico
 * de solicitações já enviadas pelo representante logado.
 * 
 * Cada solicitação possui:
 *   - Título
 *   - Descrição
 *   - Status (pendente, deferido, indeferido, etc.)
 *   - Data de criação
 * 
 * O representante pode enviar novas solicitações, mas não pode
 * alterar o status delas (apenas o administrador).
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
$titulo = 'Solicitações';

// Inclui o cabeçalho do painel (barra superior, menu lateral, abertura do main-content)
require __DIR__ . '/../../partials/dashboard_header.php';

// Mensagem de sucesso (definida pelo controller após envio)
$sucesso = $sucesso ?? null;
?>

<h1>Minhas Solicitações</h1>

<!-- Mensagem de confirmação após envio bem-sucedido -->
<?php if ($sucesso): ?>
    <div class="mensagem sucesso"><?= htmlspecialchars($sucesso) ?></div>
<?php endif; ?>

<!-- ==================== FORMULÁRIO DE NOVA SOLICITAÇÃO ==================== -->
<h2>Nova Solicitação</h2>
<form method="POST" action="/painel/solicitacoes/enviar">
    <label>Título:
        <input type="text" name="titulo" required>
    </label>
    
    <label>Descrição:
        <textarea name="descricao" rows="5" required></textarea>
    </label>
    
    <button type="submit" class="btn-primary">Enviar</button>
</form>

<!-- ==================== HISTÓRICO DE SOLICITAÇÕES ==================== -->
<h2>Histórico</h2>
<table>
    <tr>
        <th>Título</th>
        <th>Status</th>
        <th>Data</th>
    </tr>
    <?php foreach ($solicitacoes as $s): ?>
    <tr>
        <td><?= htmlspecialchars($s['titulo']) ?></td>
        <!-- Exibe o status formatado: substitui underscores por espaços e capitaliza -->
        <td><?= ucfirst(str_replace('_', ' ', $s['status'])) ?></td>
        <td><?= date('d/m/Y', strtotime($s['criado_em'])) ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<?php
// Inclui o rodapé do painel (fecha main-content, div wrapper, body e html)
require __DIR__ . '/../../partials/dashboard_footer.php';
?>