<?php
/**
 * Arquivo: Views/painel/licencas/listar.php
 * Função: VIEW de listagem de licenças dos clientes (painel do representante).
 * 
 * Exibe uma tabela com as licenças de todos os clientes vinculados ao
 * representante logado. As informações são apenas para consulta:
 *   - Nome do cliente, CPF/CNPJ
 *   - Chave da licença (parcial)
 *   - Data de expiração (com alerta a partir do dia 28)
 *   - Valor total atual (calculado dinamicamente com base no salário mínimo)
 *   - Status (Ativa / Expirada / Inativa)
 * 
 * O representante NÃO pode renovar nem gerar token offline nesta tela.
 * Essas ações foram movidas para o painel do administrador.
 */

// Garante que a variável $licencas seja sempre um array
if (!isset($licencas) || !is_array($licencas)) {
    $licencas = [];
}

// Título da página
$titulo = 'Licenças dos Meus Clientes';

// Inclui o cabeçalho comum (HTML, CSS, favicon)
require __DIR__ . '/../../partials/header.php';
?>

<h1>Licenças dos Meus Clientes</h1>
<p>
    <a href="/painel/clientes" class="btn">Voltar para Clientes</a>
    <a href="/logout" class="btn">Sair</a>
</p>

<!-- Tabela de licenças -->
<table>
    <tr>
        <th>Cliente</th>
        <th>CPF/CNPJ</th>
        <th>Chave</th>
        <th>Expiração</th>
        <th>Valor Total</th>
        <th>Status</th>
    </tr>
    <?php foreach ($licencas as $l):
        // Calcula se a licença está expirada ou em alerta (a partir do dia 28 do mês)
        $dataExp = new DateTime($l['data_expiracao']);
        $hoje = new DateTime();
        $expirada = $dataExp < $hoje;
        $diaAtual = (int)$hoje->format('d');
        $alerta = !$expirada && $diaAtual >= 28; // alerta a partir do dia 28
    ?>
    <tr>
        <td><?= htmlspecialchars($l['cliente_nome']) ?></td>
        <td><?= htmlspecialchars($l['cpf_cnpj']) ?></td>
        <td><code><?= substr($l['chave'], 0, 16) ?>...</code></td>
        <!-- Célula de expiração com classes CSS para cores -->
        <td class="<?= $expirada ? 'expirada' : ($alerta ? 'alerta' : '') ?>">
            <?= $dataExp->format('d/m/Y') ?>
            <?php if ($alerta): ?> ⚠️<?php endif; ?>
        </td>
        <!-- Valor total calculado dinamicamente (controller) -->
        <td>R$ <?= number_format($l['valor_total_atual'] ?? 0, 2, ',', '.') ?></td>
        <td class="<?= $l['ativa'] && !$expirada ? 'ativa' : 'expirada' ?>">
            <?= $l['ativa'] && !$expirada ? 'Ativa' : 'Expirada/Inativa' ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>