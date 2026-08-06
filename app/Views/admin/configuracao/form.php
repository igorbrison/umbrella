<?php
/**
 * Arquivo: Views/admin/configuracao/form.php
 * Função: VIEW de configuração do salário mínimo (painel admin).
 * 
 * Permite ao administrador visualizar e atualizar o valor do salário
 * mínimo, que é utilizado como base para o cálculo dos preços dos
 * módulos contratados pelos clientes.
 * 
 * Apenas o administrador tem acesso a esta tela.
 */

// Garante que a variável $salarioMinimo esteja sempre definida
if (!isset($salarioMinimo)) {
    $salarioMinimo = 0.0;
}

// Título da página
$titulo = 'Configuração - Salário Mínimo';

// Inclui o cabeçalho comum (HTML, CSS, favicon)
require __DIR__ . '/../partials/header.php';
?>

<h1>Salário Mínimo Atual</h1>

<!-- FORMULÁRIO DE ATUALIZAÇÃO DO SALÁRIO MÍNIMO -->
<form method="POST" action="/admin/configuracao/salvar">
    <label>Valor (R$):
        <input type="number" step="0.01" name="salario_minimo" value="<?= $salarioMinimo ?>">
    </label>
    <button type="submit">Atualizar</button>
</form>

</body>
</html>