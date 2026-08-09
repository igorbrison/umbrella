<?php
/**
 * Arquivo: Views/admin/modulos/form.php
 * Função: VIEW de formulário para criação/edição de módulos (painel admin).
 * 
 * Permite ao administrador cadastrar um novo módulo ou editar um existente.
 * O valor do módulo é calculado a partir do percentual do salário mínimo.
 */

if (!isset($modulo) || !is_array($modulo)) {
    $modulo = [];
}
if (!isset($salarioMinimo)) {
    $salarioMinimo = 1621.00;
}

$modoEdicao = !empty($modulo);
$titulo = $modoEdicao ? 'Editar Módulo' : 'Novo Módulo';

$valorAtual = 0.0;
if ($modoEdicao && isset($modulo['percentual_salario_minimo']) && $modulo['percentual_salario_minimo'] !== null) {
    $valorAtual = round($salarioMinimo * $modulo['percentual_salario_minimo'] / 100, 2);
}

require __DIR__ . '/../../partials/dashboard_header.php';
?>

<h1><?= $titulo ?></h1>

<form method="POST" action="/admin/modulos/salvar">
    <input type="hidden" name="id" value="<?= $modoEdicao ? $modulo['id'] : '' ?>">

    <fieldset>
        <legend>Dados do Módulo</legend>
        <div class="form-row">
            <div class="form-col">
                <label>Identificador <span class="obrigatorio">*</span>:
                    <input type="text" name="identificador" required
                           value="<?= $modoEdicao ? htmlspecialchars($modulo['identificador']) : '' ?>"
                           placeholder="Ex: vendas, estoque, financeiro">
                </label>
            </div>
            <div class="form-col">
                <label>Nome <span class="obrigatorio">*</span>:
                    <input type="text" name="nome" required
                           value="<?= $modoEdicao ? htmlspecialchars($modulo['nome']) : '' ?>"
                           placeholder="Ex: Módulo de Vendas">
                </label>
            </div>
        </div>

        <div class="form-row">
            <div class="form-col">
                <label>Percentual do Salário Mínimo (%):
                    <input type="number" step="0.01" name="percentual"
                           value="<?= $modoEdicao ? htmlspecialchars($modulo['percentual_salario_minimo']) : '' ?>"
                           placeholder="Ex: 10.00">
                </label>
                <span class="info">
                    Salário mínimo atual: R$ <?= number_format($salarioMinimo, 2, ',', '.') ?>
                    <?php if ($modoEdicao && $valorAtual > 0): ?>
                        | Valor calculado: <strong>R$ <?= number_format($valorAtual, 2, ',', '.') ?></strong>
                    <?php endif; ?>
                </span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-col">
                <label>Descrição:
                    <textarea name="descricao" rows="4"><?= $modoEdicao ? htmlspecialchars($modulo['descricao']) : '' ?></textarea>
                </label>
            </div>
        </div>

        <div class="form-row">
            <div class="form-col">
                <label class="checkbox-inline">
                    <input type="checkbox" name="ativo" <?= (!$modoEdicao || $modulo['ativo']) ? 'checked' : '' ?>>
                    Ativo
                </label>
            </div>
        </div>
    </fieldset>

    <div class="form-actions">
        <a href="/admin/modulos" class="btn">Cancelar</a>
        <button type="submit" class="btn-primary">Salvar</button>
    </div>
</form>

<?php require __DIR__ . '/../../partials/dashboard_footer.php'; ?>