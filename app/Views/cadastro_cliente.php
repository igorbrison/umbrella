<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Cliente</title>
</head>
<body>
    <h1>Novo Cliente</h1>
    <form method="POST" action="/cliente/salvar">
        <label>Tipo:
            <select name="tipo_pessoa">
                <option value="F">Pessoa Física</option>
                <option value="J">Pessoa Jurídica</option>
            </select>
        </label><br><br>
        <label>CPF/CNPJ: <input type="text" name="cpf_cnpj"></label><br><br>
        <label>Nome/Razão Social: <input type="text" name="nome_razao"></label><br><br>
        <label>Email: <input type="email" name="email"></label><br><br>
        <label>Telefone: <input type="text" name="telefone"></label><br><br>
        <button type="submit">Cadastrar</button>
    </form>
</body>
</html>