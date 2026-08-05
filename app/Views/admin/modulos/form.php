<?php
/**
 * View: Formulário de criação/edição de módulo (painel admin)
 * 
 * Esta view é utilizada tanto para criar um novo módulo quanto para editar um existente.
 * O controller deve fornecer as variáveis $modulo e $salarioMinimo.
 */

// Inicializações seguras para evitar avisos de análise
if (!isset($modulo) || !is_array($modulo)) {
    $modulo = [];
}
if (!isset($salarioMinimo)) {
    $salarioMinimo = 1621.00; // valor padrão apenas para fallback
}

$modoEdicao = !empty($modulo);
$titulo = $modoEdicao ? 'Editar Módulo' : 'Novo Módulo';

// Calcula o valor atual baseado no percentual e salário mínimo
$valorAtual = 0.0;
if ($modoEdicao && isset($modulo['percentual_salario_minimo']) && $modulo['percentual_salario_minimo'] !== null) {
    $valorAtual = round($salarioMinimo * $modulo['percentual_salario_minimo'] / 100, 2);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= $titulo ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <!--
    <style>
        label { display: block; margin-top: 10px; }
        .obrigatorio { color: red; font-weight: bold; margin-left: 3px; }
        .info { font-size: 0.85em; color: #555; margin-left: 5px; }
    </style>
-->
</head>
<body>
    <h1><?= $titulo ?></h1>

    <form method="POST" action="/admin/modulos/salvar">
        <!-- Campo oculto com o ID: se estiver presente, é uma edição -->
        <input type="hidden" name="id" value="<?= $modoEdicao ? $modulo['id'] : '' ?>">

        <label>Identificador <span class="obrigatorio">*</span>:
            <input type="text" name="identificador" required
                   value="<?= $modoEdicao ? htmlspecialchars($modulo['identificador']) : '' ?>"
                   placeholder="Ex: vendas, estoque, financeiro">
            <span class="info">(Usado internamente pelo sistema)</span>
        </label>

        <label>Nome <span class="obrigatorio">*</span>:
            <input type="text" name="nome" required
                   value="<?= $modoEdicao ? htmlspecialchars($modulo['nome']) : '' ?>"
                   placeholder="Ex: Módulo de Vendas">
        </label>

        <!-- CAMPO ATUALIZADO: Percentual do Salário Mínimo -->
        <label>Percentual do Salário Mínimo (%):
            <input type="number" step="0.01" name="percentual"
                   value="<?= $modoEdicao ? htmlspecialchars($modulo['percentual_salario_minimo']) : '' ?>"
                   placeholder="Ex: 10.00">
            <span class="info">
                Salário mínimo atual: R$ <?= number_format($salarioMinimo, 2, ',', '.') ?>
                <?php if ($modoEdicao && $valorAtual > 0): ?>
                    | Valor calculado: <strong>R$ <?= number_format($valorAtual, 2, ',', '.') ?></strong>
                <?php endif; ?>
            </span>
        </label>

        <label>Descrição:
            <textarea name="descricao" rows="4"><?= $modoEdicao ? htmlspecialchars($modulo['descricao']) : '' ?></textarea>
        </label>

        <label>Ativo:
            <input type="checkbox" name="ativo" <?= (!$modoEdicao || $modulo['ativo']) ? 'checked' : '' ?>>
        </label>

        <br>
        <button type="submit">Salvar</button>
        <a href="/admin/modulos">Cancelar</a>
    </form>
</body>
</html>