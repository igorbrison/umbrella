<?php
/**
 * Arquivo: Views/admin/modulos/form.php
 * Função: VIEW de formulário para criação/edição de módulos (painel admin).
 * 
 * Permite ao administrador cadastrar um novo módulo ou editar um existente.
 * 
 * Características:
 *   - O campo "Percentual do Salário Mínimo" define o preço do módulo.
 *   - O valor calculado em reais é exibido dinamicamente ao lado do campo,
 *     com base no salário mínimo vigente (cadastrado nas configurações).
 *   - Apenas o administrador tem acesso a esta tela.
 * 
 * O controller deve fornecer as variáveis $modulo (array com dados) e $salarioMinimo (float).
 */

// Inicializações seguras para evitar avisos de análise
if (!isset($modulo) || !is_array($modulo)) {
    $modulo = [];
}
if (!isset($salarioMinimo)) {
    $salarioMinimo = 1621.00; // fallback para desenvolvimento
}

// Define o modo de edição e o título da página
$modoEdicao = !empty($modulo);
$titulo = $modoEdicao ? 'Editar Módulo' : 'Novo Módulo';

// Calcula o valor atual com base no percentual e salário mínimo (apenas no modo edição)
$valorAtual = 0.0;
if ($modoEdicao && isset($modulo['percentual_salario_minimo']) && $modulo['percentual_salario_minimo'] !== null) {
    $valorAtual = round($salarioMinimo * $modulo['percentual_salario_minimo'] / 100, 2);
}

// Inclui o cabeçalho comum (HTML, CSS, favicon)
require __DIR__ . '/../partials/header.php';
?>

<h1><?= $titulo ?></h1>

<!-- FORMULÁRIO DE MÓDULO -->
<form method="POST" action="/admin/modulos/salvar">
    <!-- ID oculto para identificar edição -->
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

    <!-- PERCENTUAL DO SALÁRIO MÍNIMO -->
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