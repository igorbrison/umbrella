<?php
// Garante que $salarioMinimo esteja sempre definida
if (!isset($salarioMinimo)) {
    $salarioMinimo = 0.0;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Configuração - Salário Mínimo</title>
</head>
<body>
    <h1>Salário Mínimo Atual</h1>
    <form method="POST" action="/admin/configuracao/salvar">
        <label>Valor (R$): <input type="number" step="0.01" name="salario_minimo" value="<?= $salarioMinimo ?>"></label>
        <button type="submit">Atualizar</button>
    </form>
</body>
</html>