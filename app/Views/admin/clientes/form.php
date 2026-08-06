<?php
/**
 * Arquivo: Views/admin/clientes/form.php
 * Função: VIEW de formulário para edição de clientes (painel admin).
 * 
 * Permite ao administrador editar completamente os dados de um cliente,
 * inclusive alterar os módulos contratados (privilégio exclusivo do admin).
 * 
 * Acesso restrito ao perfil 'admin'. O dashboard_header já verifica
 * a autenticação e redireciona se necessário.
 * 
 * Uso dos parciais:
 *   - dashboard_header.php: barra superior, menu lateral e abertura do main-content.
 *   - dashboard_footer.php: fechamento das tags abertas pelo header.
 */

// Inicializações seguras para evitar avisos de análise
if (!isset($cliente) || !is_array($cliente)) {
    $cliente = [];
}
if (!isset($modulos)) {
    $modulos = [];
}
if (!isset($idsModulosCliente)) {
    $idsModulosCliente = [];
}

// Define o modo de edição e o título da página
$modoEdicao = !empty($cliente);
$titulo = $modoEdicao ? 'Editar Cliente (Admin)' : 'Novo Cliente';

// Mensagem de erro opcional (definida pelo controller em caso de falha)
$erro = $erro ?? null;

// Inclui o cabeçalho do painel (barra superior, menu lateral, abertura do main-content)
require __DIR__ . '/../../partials/dashboard_header.php';
?>

<h1><?= $titulo ?></h1>

<!-- Exibe mensagem de erro, se houver -->
<?php if ($erro): ?>
    <div class="erro-msg"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>

<!-- FORMULÁRIO DE EDIÇÃO DE CLIENTE (ADMIN) -->
<form method="POST" action="/admin/clientes/salvar" id="form-cliente">
    <input type="hidden" name="id" value="<?= $modoEdicao ? $cliente['id'] : '' ?>">

    <!-- ===================== DADOS PRINCIPAIS ===================== -->
    <fieldset><legend>Dados do Cliente</legend>
        <label>Tipo Pessoa <span class="obrigatorio">*</span>:
            <select name="tipo_pessoa" id="tipo_pessoa" required>
                <option value="">Selecione...</option>
                <option value="F" <?= $modoEdicao && ($cliente['tipo_pessoa'] ?? '') == 'F' ? 'selected' : '' ?>>Física</option>
                <option value="J" <?= $modoEdicao && ($cliente['tipo_pessoa'] ?? '') == 'J' ? 'selected' : '' ?>>Jurídica</option>
            </select>
        </label>

        <label>CPF/CNPJ <span class="obrigatorio">*</span>:
            <input type="text" name="cpf_cnpj" id="cpf_cnpj" required
                   value="<?= $modoEdicao ? htmlspecialchars($cliente['cpf_cnpj']) : '' ?>"
                   maxlength="18">
        </label>
        <button type="button" id="btn-buscar-cnpj" style="display:none;">Buscar dados pelo CNPJ</button>
        <span id="loading-cnpj" style="display:none;">Buscando...</span>

        <label id="label-ie-rg"><span id="texto-ie-rg">Inscrição Estadual</span> <span class="obrigatorio">*</span>:
            <input type="text" name="ie_rg" id="ie_rg" required
                   maxlength="14"
                   value="<?= $modoEdicao ? htmlspecialchars($cliente['ie_rg'] ?? '') : '' ?>">
        </label>

        <label>Nome / Razão Social <span class="obrigatorio">*</span>:
            <input type="text" name="nome" required
                   value="<?= $modoEdicao ? htmlspecialchars($cliente['nome']) : '' ?>">
        </label>

        <label id="label-nome-fantasia">Nome Fantasia <span class="obrigatorio">*</span>:
            <input type="text" name="nome_fantasia" id="nome_fantasia" required
                   value="<?= $modoEdicao ? htmlspecialchars($cliente['nome_fantasia'] ?? '') : '' ?>">
        </label>

        <label id="label-data-fundacao">Data de Fundação <span class="obrigatorio">*</span>:
            <input type="date" name="data_fundacao" id="data_fundacao" required
                   value="<?= $modoEdicao ? ($cliente['data_fundacao'] ?? '') : '' ?>">
        </label>
    </fieldset>

    <!-- ===================== ENDEREÇO ===================== -->
    <fieldset class="grupo"><legend>Endereço</legend>
        <label>CEP <span class="obrigatorio">*</span>:
            <input type="text" name="cep" id="cep" maxlength="9" required
                   value="<?= $modoEdicao ? htmlspecialchars($cliente['cep'] ?? '') : '' ?>">
        </label>
        <button type="button" id="btn-buscar-cep">Buscar endereço pelo CEP</button>
        <span id="loading-cep" style="display:none;">Buscando...</span>

        <label>Logradouro <span class="obrigatorio">*</span>:
            <input type="text" name="logradouro" required
                   value="<?= $modoEdicao ? htmlspecialchars($cliente['logradouro'] ?? '') : '' ?>">
        </label>
        <label>Número <span class="obrigatorio">*</span>:
            <input type="text" name="numero" required
                   value="<?= $modoEdicao ? htmlspecialchars($cliente['numero'] ?? '') : '' ?>">
        </label>
        <label>Complemento:
            <input type="text" name="complemento"
                   value="<?= $modoEdicao ? htmlspecialchars($cliente['complemento'] ?? '') : '' ?>">
        </label>
        <label>Bairro <span class="obrigatorio">*</span>:
            <input type="text" name="bairro" required
                   value="<?= $modoEdicao ? htmlspecialchars($cliente['bairro'] ?? '') : '' ?>">
        </label>
        <label>Estado <span class="obrigatorio">*</span>:
            <input type="text" name="estado" maxlength="2" required
                   value="<?= $modoEdicao ? htmlspecialchars($cliente['estado'] ?? '') : '' ?>">
        </label>
        <label>Município <span class="obrigatorio">*</span>:
            <input type="text" name="municipio" required
                   value="<?= $modoEdicao ? htmlspecialchars($cliente['municipio'] ?? '') : '' ?>">
        </label>
    </fieldset>

    <!-- ===================== CONTATO ===================== -->
    <fieldset class="grupo"><legend>Contato</legend>
        <label>Telefone <span class="obrigatorio">*</span>:
            <input type="text" name="telefone" required
                   value="<?= $modoEdicao ? htmlspecialchars($cliente['telefone'] ?? '') : '' ?>">
        </label>
        <label>Celular <span class="obrigatorio">*</span>:
            <input type="text" name="celular" required
                   value="<?= $modoEdicao ? htmlspecialchars($cliente['celular'] ?? '') : '' ?>">
        </label>
        <label>Email <span class="obrigatorio">*</span>:
            <input type="email" name="email" required
                   value="<?= $modoEdicao ? htmlspecialchars($cliente['email'] ?? '') : '' ?>">
        </label>
    </fieldset>

    <!-- ===================== OBSERVAÇÕES E STATUS ===================== -->
    <fieldset class="grupo">
        <label>Observações:
            <textarea name="observacoes" rows="4"><?= $modoEdicao ? htmlspecialchars($cliente['observacoes'] ?? '') : '' ?></textarea>
        </label>
        <label>Ativo:
            <input type="checkbox" name="ativo" <?= (!$modoEdicao || ($cliente['ativo'] ?? 1)) ? 'checked' : '' ?>>
        </label>
    </fieldset>

    <!-- ===================== MÓDULOS CONTRATADOS ===================== -->
    <!-- Admin sempre pode alterar os módulos -->
    <fieldset class="grupo">
        <legend>Módulos Contratados (Admin pode alterar)</legend>
        <?php foreach ($modulos as $m): ?>
            <label>
                <input type="checkbox" name="modulos[]" value="<?= $m['id'] ?>"
                <?= in_array($m['identificador'], $idsModulosCliente) ? 'checked' : '' ?>>
                <?= htmlspecialchars($m['nome']) ?> (<?= htmlspecialchars($m['identificador']) ?>)
                - R$ <?= number_format($m['valor'] ?? 0, 2, ',', '.') ?>
            </label><br>
        <?php endforeach; ?>
    </fieldset>

    <button type="submit">Salvar</button>
    <a href="/admin/licencas">Cancelar</a>
</form>

<!-- ===================== JAVASCRIPT ===================== -->
<script>
    const tipoSelect = document.getElementById('tipo_pessoa');
    const cpfCnpjInput = document.getElementById('cpf_cnpj');
    const btnBuscarCnpj = document.getElementById('btn-buscar-cnpj');
    const loadingCnpj = document.getElementById('loading-cnpj');
    const ieRgInput = document.getElementById('ie_rg');
    const labelIeRg = document.getElementById('label-ie-rg');
    const textoIeRg = document.getElementById('texto-ie-rg');
    const labelNomeFantasia = document.getElementById('label-nome-fantasia');
    const nomeFantasiaInput = document.getElementById('nome_fantasia');
    const labelDataFundacao = document.getElementById('label-data-fundacao');
    const dataFundacaoInput = document.getElementById('data_fundacao');
    const cepInput = document.getElementById('cep');
    const btnBuscarCep = document.getElementById('btn-buscar-cep');
    const loadingCep = document.getElementById('loading-cep');

    function ajustarCamposPessoa() {
        const tipo = tipoSelect.value;
        if (tipo === 'F') {
            cpfCnpjInput.maxLength = 14;
            cpfCnpjInput.placeholder = '000.000.000-00';
            btnBuscarCnpj.style.display = 'none';
            textoIeRg.textContent = 'RG';
            ieRgInput.placeholder = 'RG (até 12 dígitos)';
            ieRgInput.maxLength = 12;
            labelNomeFantasia.style.display = 'none';
            nomeFantasiaInput.removeAttribute('required');
            labelDataFundacao.style.display = 'none';
            dataFundacaoInput.removeAttribute('required');
        } else if (tipo === 'J') {
            cpfCnpjInput.maxLength = 18;
            cpfCnpjInput.placeholder = '00.000.000/0000-00';
            btnBuscarCnpj.style.display = 'inline-block';
            textoIeRg.textContent = 'Inscrição Estadual';
            ieRgInput.placeholder = 'Inscrição Estadual (até 14 dígitos)';
            ieRgInput.maxLength = 14;
            labelNomeFantasia.style.display = 'block';
            nomeFantasiaInput.setAttribute('required', 'required');
            labelDataFundacao.style.display = 'block';
            dataFundacaoInput.setAttribute('required', 'required');
        } else {
            cpfCnpjInput.maxLength = 18;
            cpfCnpjInput.placeholder = '';
            btnBuscarCnpj.style.display = 'none';
            textoIeRg.textContent = 'Inscrição Estadual / RG';
            ieRgInput.placeholder = '';
            ieRgInput.maxLength = 14;
            labelNomeFantasia.style.display = 'none';
            nomeFantasiaInput.removeAttribute('required');
            labelDataFundacao.style.display = 'none';
            dataFundacaoInput.removeAttribute('required');
        }
    }

    tipoSelect.addEventListener('change', ajustarCamposPessoa);
    ajustarCamposPessoa();

    function converterData(dataStr) {
        if (!dataStr) return '';
        const partes = dataStr.split('/');
        if (partes.length !== 3) return '';
        return `${partes[2]}-${partes[1].padStart(2, '0')}-${partes[0].padStart(2, '0')}`;
    }

    btnBuscarCnpj.addEventListener('click', function() {
        let cnpj = cpfCnpjInput.value.replace(/\D/g, '');
        if (cnpj.length !== 14) {
            alert('Digite um CNPJ completo (14 números).');
            return;
        }
        loadingCnpj.style.display = 'inline';
        fetch('/api/buscar_cnpj.php?cnpj=' + cnpj)
            .then(response => response.json())
            .then(data => {
                loadingCnpj.style.display = 'none';
                if (data.status === 'ERROR') {
                    alert(data.message || 'CNPJ não encontrado ou inválido.');
                    return;
                }
                document.querySelector('input[name="nome"]').value = data.nome || '';
                document.querySelector('input[name="nome_fantasia"]').value = data.fantasia || '';
                document.querySelector('input[name="logradouro"]').value = data.logradouro || '';
                document.querySelector('input[name="numero"]').value = data.numero || '';
                document.querySelector('input[name="complemento"]').value = data.complemento || '';
                document.querySelector('input[name="bairro"]').value = data.bairro || '';
                document.querySelector('input[name="cep"]').value = data.cep || '';
                document.querySelector('input[name="municipio"]').value = data.municipio || '';
                document.querySelector('input[name="estado"]').value = data.uf || '';
                document.querySelector('input[name="telefone"]').value = data.telefone || '';
                document.querySelector('input[name="email"]').value = data.email || '';
                if (data.abertura) {
                    document.getElementById('data_fundacao').value = converterData(data.abertura);
                }
            })
            .catch(error => {
                loadingCnpj.style.display = 'none';
                alert('Erro ao buscar CNPJ. Tente novamente.');
            });
    });

    btnBuscarCep.addEventListener('click', function() {
        let cep = cepInput.value.replace(/\D/g, '');
        if (cep.length !== 8) {
            alert('Digite um CEP válido (8 números).');
            return;
        }
        loadingCep.style.display = 'inline';
        fetch('/api/buscar_cep.php?cep=' + cep)
            .then(response => response.json())
            .then(data => {
                loadingCep.style.display = 'none';
                if (data.erro) {
                    alert('CEP não encontrado.');
                    return;
                }
                document.querySelector('input[name="logradouro"]').value = data.logradouro || '';
                document.querySelector('input[name="bairro"]').value = data.bairro || '';
                document.querySelector('input[name="municipio"]').value = data.localidade || '';
                document.querySelector('input[name="estado"]').value = data.uf || '';
                document.querySelector('input[name="complemento"]').value = data.complemento || '';
            })
            .catch(error => {
                loadingCep.style.display = 'none';
                alert('Erro ao buscar CEP. Tente novamente.');
            });
    });
</script>

<?php
// Inclui o rodapé do painel (fecha main-content, div wrapper, body e html)
require __DIR__ . '/../../partials/dashboard_footer.php';
?>