<?php
/**
 * Relatório de comissão detalhado por cliente (HTML imprimível).
 */

// Inicializações seguras
if (!isset($rep) || !is_array($rep)) $rep = [];
if (!isset($clientes) || !is_array($clientes)) $clientes = [];
if (!isset($totalComissao)) $totalComissao = 0;
if (!isset($totalPago)) $totalPago = 0;
if (!isset($pagamentos)) $pagamentos = [];

$mesReferencia = $_GET['mes'] ?? date('Y-m', strtotime('first day of last month'));
$dataFormatada = DateTime::createFromFormat('Y-m', $mesReferencia)->format('m/Y');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Comissão – <?= htmlspecialchars($rep['nome_razao'] ?? '') ?></title>
    <style>
        @page {
            size: A4;
            margin: 2cm;
            @bottom-center {
                content: "Página " counter(page) " de " counter(pages);
                font-size: 10px;
                color: #666;
            }
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 30px 40px;
            color: #1a2a3a;
            counter-reset: page;
            padding-bottom: 40px;          /* espaço para não cobrir o rodapé */
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #e0e5ec;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0 0 4px 0;
            font-size: 26px;
        }

        .header .info p {
            margin: 4px 0;
            font-size: 15px;
        }

        .resumo {
            display: flex;
            gap: 30px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .resumo .box {
            background: #f8fafc;
            border: 1px solid #e0e5ec;
            border-radius: 8px;
            padding: 12px 20px;
            flex: 1;
            min-width: 180px;
        }

        .resumo .box strong {
            display: block;
            font-size: 14px;
            color: #5a6c7e;
            margin-bottom: 4px;
        }

        .resumo .box span {
            font-size: 22px;
            font-weight: 600;
            color: #1a2a3a;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 14px;
        }

        th {
            background: #f0f4f8;
            text-align: left;
            padding: 10px 12px;
            border-bottom: 2px solid #e0e5ec;
        }

        td {
            padding: 8px 12px;
            border-bottom: 1px solid #eef2f7;
        }

        .pago-list {
            margin-top: 30px;
            margin-bottom: 50px;            /* espaço extra antes do rodapé */
        }

        .pago-list h3 {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .btn-editar-obs {
            cursor: pointer;
            color: #2563eb;
            background: none;
            border: none;
            font-size: 14px;
            text-decoration: underline;
        }

        .page-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #666;
            padding: 10px 0;
            border-top: 1px solid #e0e5ec;
            background: white;
        }

        @media print {
            .no-print { display: none; }
            body { margin: 20px; padding-bottom: 40px; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>Relatório de Comissão</h1>
            <p style="font-size:16px; color:#5a6c7e;"><?= $dataFormatada ?></p>
        </div>
        <div class="info">
            <p><strong><?= htmlspecialchars($rep['nome_razao'] ?? '') ?></strong></p>
            <p>CNPJ: <?= htmlspecialchars($rep['cnpj'] ?? '') ?></p>
            <p>Comissão: <?= number_format((float)($rep['comissao_percentual'] ?? 0), 2, ',', '.') ?>%</p>
        </div>
    </div>

    <div class="resumo">
        <div class="box">
            <strong>Total Devido</strong>
            <span>R$ <?= number_format($totalComissao, 2, ',', '.') ?></span>
        </div>
        <div class="box">
            <strong>Total Pago</strong>
            <span>R$ <?= number_format($totalPago, 2, ',', '.') ?></span>
        </div>
        <div class="box">
            <strong>Saldo a Pagar</strong>
            <span>R$ <?= number_format($totalComissao - $totalPago, 2, ',', '.') ?></span>
        </div>
    </div>

    <h3>Comissões por Cliente</h3>
    <table>
        <thead>
            <tr>
                <th>Cliente</th>
                <th>CPF/CNPJ</th>
                <th>Total Pago pelo Cliente (R$)</th>
                <th>Comissão (R$)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $somaTotalPago = 0;
            $somaComissao = 0;
            foreach ($clientes as $cli):
                $somaTotalPago += (float)($cli['total_pago'] ?? 0);
                $somaComissao += (float)($cli['comissao'] ?? 0);
            ?>
            <tr>
                <td><?= htmlspecialchars($cli['cliente_nome'] ?? '') ?></td>
                <td><?= htmlspecialchars($cli['cpf_cnpj'] ?? '') ?></td>
                <td>R$ <?= number_format((float)($cli['total_pago'] ?? 0), 2, ',', '.') ?></td>
                <td>R$ <?= number_format((float)($cli['comissao'] ?? 0), 2, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background: #f8fafc;">
                <td colspan="2">Totais</td>
                <td>R$ <?= number_format($somaTotalPago, 2, ',', '.') ?></td>
                <td>R$ <?= number_format($somaComissao, 2, ',', '.') ?></td>
            </tr>
        </tfoot>
    </table>

    <?php if (!empty($pagamentos)): ?>
    <div class="pago-list">
        <h3>Pagamentos Efetuados ao Representante</h3>
        <table>
            <thead>
                <tr>
                    <th>Valor (R$)</th>
                    <th>Observação</th>
                    <th class="no-print">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pagamentos as $pg): ?>
                <tr>
                    <td>R$ <?= number_format((float)$pg['valor'], 2, ',', '.') ?></td>
                    <td>
                        <span id="obs-text-<?= $pg['id'] ?>"><?= htmlspecialchars($pg['observacao'] ?? '') ?></span>
                        <span id="obs-edit-<?= $pg['id'] ?>" style="display:none;">
                            <input type="text" id="obs-input-<?= $pg['id'] ?>" value="<?= htmlspecialchars($pg['observacao'] ?? '') ?>" style="width:200px;">
                            <button onclick="salvarObs(<?= $pg['id'] ?>)" class="btn-primary" style="padding:2px 8px;font-size:13px;">Salvar</button>
                            <button onclick="cancelarObs(<?= $pg['id'] ?>)" class="btn" style="padding:2px 8px;font-size:13px;">Cancelar</button>
                        </span>
                    </td>
                    <td class="no-print">
                        <button class="btn-editar-obs" onclick="editarObs(<?= $pg['id'] ?>)">Editar</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <p class="no-print" style="margin-top:20px;">
        <button onclick="window.print()">Imprimir / Salvar como PDF</button>
        <button onclick="window.close()">Fechar</button>
    </p>

    <script>
        function editarObs(id) {
            document.getElementById('obs-text-' + id).style.display = 'none';
            document.getElementById('obs-edit-' + id).style.display = 'inline';
        }
        function cancelarObs(id) {
            document.getElementById('obs-text-' + id).style.display = 'inline';
            document.getElementById('obs-edit-' + id).style.display = 'none';
        }
        function salvarObs(id) {
            const novoValor = document.getElementById('obs-input-' + id).value;
            fetch('/admin/representantes/comissoes/editar-observacao', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + id + '&observacao=' + encodeURIComponent(novoValor)
            })
            .then(r => r.json())
            .then(data => {
                if (data.ok) {
                    document.getElementById('obs-text-' + id).textContent = novoValor;
                    cancelarObs(id);
                } else {
                    alert('Erro ao salvar.');
                }
            });
        }
    </script>
</body>
</html>