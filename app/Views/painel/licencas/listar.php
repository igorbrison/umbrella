<?php
if (!isset($licencas) || !is_array($licencas)) {
    $licencas = [];
}
$tokenGerado = $_SESSION['token_gerado'] ?? null;
$erroToken = $_SESSION['erro_token'] ?? null;
// Limpa a sessão
unset($_SESSION['token_gerado'], $_SESSION['erro_token']);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Licenças dos Clientes</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; }
        th { background: #f5f5f5; }
        .btn { padding: 5px 10px; text-decoration: none; background: #eee; border:1px solid #aaa; border-radius:3px; margin-right: 5px; }
        .expirada { color: red; font-weight: bold; }
        .alerta { color: orange; font-weight: bold; }
        .ativa { color: green; }
    </style>
</head>
<body>
    <h1>Licenças dos Meus Clientes</h1>
    <p>
        <a href="/painel/clientes" class="btn">Voltar para Clientes</a>
        <a href="/logout" class="btn">Sair</a>
    </p>

    <?php if ($tokenGerado): ?>
        <div style="background: #dff0d8; border: 1px solid #3c763d; padding: 10px; margin: 10px 0;">
            <strong>Token Offline Gerado:</strong><br>
            <textarea rows="3" style="width:100%;" readonly><?= htmlspecialchars($tokenGerado) ?></textarea>
            <small>Encaminhe este token ao cliente para ativação offline.</small>
        </div>
    <?php endif; ?>
    <?php if ($erroToken): ?>
        <div style="background: #f2dede; border: 1px solid #a94442; padding: 10px; margin: 10px 0;">
            <?= htmlspecialchars($erroToken) ?>
        </div>
    <?php endif; ?>

    <table>
        <tr>
            <th>Cliente</th>
            <th>CPF/CNPJ</th>
            <th>Chave</th>
            <th>Expiração</th>
            <th>Valor Total</th>
            <th>Status</th>
            <th>Ações</th>
        </tr>
        <?php foreach ($licencas as $l): 
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
            <td class="<?= $expirada ? 'expirada' : ($alerta ? 'alerta' : '') ?>">
                <?= $dataExp->format('d/m/Y') ?>
                <?php if ($alerta): ?> ⚠️<?php endif; ?>
            </td>
            <td>R$ <?= number_format($l['valor_total'] ?? 0, 2, ',', '.') ?></td>
            <td class="<?= $l['ativa'] && !$expirada ? 'ativa' : 'expirada' ?>">
                <?= $l['ativa'] && !$expirada ? 'Ativa' : 'Expirada/Inativa' ?>
            </td>
            <td>
                <a href="/painel/licencas/renovar/<?= $l['cliente_id'] ?>" class="btn" onclick="return confirm('Renovar licença por mais 30 dias?')">Renovar</a>
                <a href="/painel/licencas/gerar-token/<?= $l['cliente_id'] ?>" class="btn" onclick="return confirm('Gerar token offline?')">Gerar Token Offline</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>