<?php
/**
 * Arquivo: Views/admin/clientes/form.php
 * Função: VIEW de formulário para edição de clientes (painel admin).
 * 
 * Permite ao administrador editar completamente os dados de um cliente,
 * inclusive alterar os módulos contratados e a quantidade de máquinas
 * permitidas (privilégio exclusivo do admin).
 * 
 * Organizado em abas para melhor usabilidade:
 *   - Aba 1: Dados Principais (tipo pessoa, CPF/CNPJ, nome, etc.)
 *   - Aba 2: Endereço
 *   - Aba 3: Contato, Observações e Ativo
 *   - Aba 4: Módulos Contratados e Quantidade de Máquinas
 */

if (!isset($cliente) || !is_array($cliente)) {
    $cliente = [];
}
if (!isset($modulos)) {
    $modulos = [];
}
if (!isset($idsModulosCliente)) {
    $idsModulosCliente = [];
}

$modoEdicao = !empty($cliente);
$titulo = $modoEdicao ? 'Editar Cliente' : 'Novo Cliente';
$erro = $erro ?? null;

require __DIR__ . '/../../partials/dashboard_header.php';
?>

<h1><?= $titulo ?></h1>

<?php if ($erro): ?>
    <div class="erro-msg"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>

<form method="POST" action="/admin/clientes/salvar" id="form-cliente">
    <input type="hidden" name="id" value="<?= $modoEdicao ? $cliente['id'] : '' ?>">

    <div class="tabs-container">
        <div class="tabs-nav">
            <button type="button" class="tab-btn active" data-tab="tab-principal">
                <i class="fas fa-user"></i> Dados Principais
            </button>
            <button type="button" class="tab-btn" data-tab="tab-endereco">
                <i class="fas fa-map-marker-alt"></i> Endereço
            </button>
            <button type="button" class="tab-btn" data-tab="tab-contato">
                <i class="fas fa-phone"></i> Contato
            </button>
            <button type="button" class="tab-btn" data-tab="tab-modulos">
                <i class="fas fa-cubes"></i> Módulos
            </button>
        </div>

        <div class="tab-content">
            <!-- ABA 1: DADOS PRINCIPAIS -->
            <div id="tab-principal" class="tab-pane active">
                <fieldset>
                    <legend>Dados do Cliente</legend>
                    <div class="form-row">
                        <div class="form-col">
                            <label>Tipo Pessoa <span class="obrigatorio">*</span>:
                                <select name="tipo_pessoa" id="tipo_pessoa" required>
                                    <option value="">Selecione...</option>
                                    <option value="F" <?= $modoEdicao && ($cliente['tipo_pessoa'] ?? '') == 'F' ? 'selected' : '' ?>>Física</option>
                                    <option value="J" <?= $modoEdicao && ($cliente['tipo_pessoa'] ?? '') == 'J' ? 'selected' : '' ?>>Jurídica</option>
                                </select>
                            </label>
                        </div>
                        <div class="form-col">
                            <label>CPF/CNPJ <span class="obrigatorio">*</span>:
                                <input type="text" name="cpf_cnpj" id="cpf_cnpj" required
                                       value="<?= $modoEdicao ? htmlspecialchars($cliente['cpf_cnpj']) : '' ?>"
                                       maxlength="18">
                            </label>
                            <button type="button" id="btn-buscar-cnpj" style="display:none;" class="btn-buscar">Buscar dados pelo CNPJ</button>
                            <span id="loading-cnpj" style="display:none;">Buscando...</span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <label id="label-ie-rg"><span id="texto-ie-rg">Inscrição Estadual</span> <span class="obrigatorio">*</span>:
                                <input type="text" name="ie_rg" id="ie_rg" required maxlength="14"
                                       value="<?= $modoEdicao ? htmlspecialchars($cliente['ie_rg'] ?? '') : '' ?>">
                            </label>
                        </div>
                        <div class="form-col">
                            <label>Nome / Razão Social <span class="obrigatorio">*</span>:
                                <input type="text" name="nome" required
                                       value="<?= $modoEdicao ? htmlspecialchars($cliente['nome']) : '' ?>">
                            </label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <label id="label-nome-fantasia">Nome Fantasia <span class="obrigatorio">*</span>:
                                <input type="text" name="nome_fantasia" id="nome_fantasia" required
                                       value="<?= $modoEdicao ? htmlspecialchars($cliente['nome_fantasia'] ?? '') : '' ?>">
                            </label>
                        </div>
                        <div class="form-col">
                            <label id="label-data-fundacao">Data de Fundação <span class="obrigatorio">*</span>:
                                <input type="date" name="data_fundacao" id="data_fundacao" required
                                       value="<?= $modoEdicao ? ($cliente['data_fundacao'] ?? '') : '' ?>">
                            </label>
                        </div>
                    </div>
                </fieldset>
            </div>

            <!-- ABA 2: ENDEREÇO -->
            <div id="tab-endereco" class="tab-pane">
                <fieldset>
                    <legend>Endereço</legend>
                    <div class="form-row">
                        <div class="form-col">
                            <label>CEP <span class="obrigatorio">*</span>:
                                <input type="text" name="cep" id="cep" maxlength="9" required
                                       value="<?= $modoEdicao ? htmlspecialchars($cliente['cep'] ?? '') : '' ?>">
                            </label>
                            <button type="button" id="btn-buscar-cep" class="btn-buscar">Buscar endereço pelo CEP</button>
                            <span id="loading-cep" style="display:none;">Buscando...</span>
                        </div>
                        <div class="form-col">
                            <label>Logradouro <span class="obrigatorio">*</span>:
                                <input type="text" name="logradouro" required
                                       value="<?= $modoEdicao ? htmlspecialchars($cliente['logradouro'] ?? '') : '' ?>">
                            </label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <label>Número <span class="obrigatorio">*</span>:
                                <input type="text" name="numero" required
                                       value="<?= $modoEdicao ? htmlspecialchars($cliente['numero'] ?? '') : '' ?>">
                            </label>
                        </div>
                        <div class="form-col">
                            <label>Complemento:
                                <input type="text" name="complemento"
                                       value="<?= $modoEdicao ? htmlspecialchars($cliente['complemento'] ?? '') : '' ?>">
                            </label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <label>Bairro <span class="obrigatorio">*</span>:
                                <input type="text" name="bairro" required
                                       value="<?= $modoEdicao ? htmlspecialchars($cliente['bairro'] ?? '') : '' ?>">
                            </label>
                        </div>
                        <div class="form-col">
                            <label>Estado <span class="obrigatorio">*</span>:
                                <input type="text" name="estado" maxlength="2" required
                                       value="<?= $modoEdicao ? htmlspecialchars($cliente['estado'] ?? '') : '' ?>">
                            </label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <label>Município <span class="obrigatorio">*</span>:
                                <input type="text" name="municipio" required
                                       value="<?= $modoEdicao ? htmlspecialchars($cliente['municipio'] ?? '') : '' ?>">
                            </label>
                        </div>
                    </div>
                </fieldset>
            </div>

            <!-- ABA 3: CONTATO E OBSERVAÇÕES -->
            <div id="tab-contato" class="tab-pane">
                <fieldset>
                    <legend>Contato</legend>
                    <div class="form-row">
                        <div class="form-col">
                            <label>Telefone <span class="obrigatorio">*</span>:
                                <input type="text" name="telefone" id="telefone" required
                                       value="<?= $modoEdicao ? htmlspecialchars($cliente['telefone'] ?? '') : '' ?>">
                            </label>
                        </div>
                        <div class="form-col">
                            <label>Celular <span class="obrigatorio">*</span>:
                                <input type="text" name="celular" id="celular" required
                                       value="<?= $modoEdicao ? htmlspecialchars($cliente['celular'] ?? '') : '' ?>">
                            </label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <label>Email <span class="obrigatorio">*</span>:
                                <input type="email" name="email" required
                                       value="<?= $modoEdicao ? htmlspecialchars($cliente['email'] ?? '') : '' ?>">
                            </label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <label>Observações:
                                <textarea name="observacoes" rows="4"><?= $modoEdicao ? htmlspecialchars($cliente['observacoes'] ?? '') : '' ?></textarea>
                            </label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <label class="checkbox-inline">
                                <input type="checkbox" name="ativo" <?= (!$modoEdicao || ($cliente['ativo'] ?? 1)) ? 'checked' : '' ?>>
                                Ativo
                            </label>
                        </div>
                    </div>
                </fieldset>
            </div>

            <!-- ABA 4: MÓDULOS CONTRATADOS E MÁQUINAS -->
            <div id="tab-modulos" class="tab-pane">
                <fieldset>
                    <legend>Módulos Contratados</legend>
                    <?php foreach ($modulos as $m): ?>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="modulos[]" value="<?= $m['id'] ?>"
                            <?= in_array($m['identificador'], $idsModulosCliente) ? 'checked' : '' ?>>
                            <?= htmlspecialchars($m['nome']) ?> (<?= htmlspecialchars($m['identificador']) ?>)
                            - R$ <?= number_format($m['valor'] ?? 0, 2, ',', '.') ?>
                        </label><br>
                    <?php endforeach; ?>
                </fieldset>

                <fieldset>
                    <legend>Limite de Máquinas</legend>
                    <div class="form-row">
                        <div class="form-col">
                            <label>Quantidade de Máquinas Permitidas <span class="obrigatorio">*</span>:
                                <input type="number" name="qtd_maquinas" required min="1"
                                       value="<?= $cliente['qtd_maquinas'] ?? 1 ?>">
                            </label>
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="/admin/clientes" class="btn btn-limpar">Cancelar</a>
        <button type="submit" class="btn-primary">Salvar</button>
    </div>
</form>

<script>
    // ---------- MÁSCARAS ----------
    function mascaraCPF(valor) {
        return valor.replace(/\D/g, '')
            .replace(/^(\d{3})(\d)/, '$1.$2')
            .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
            .replace(/\.(\d{3})(\d)/, '.$1-$2')
            .slice(0, 14);
    }
    function mascaraCNPJ(valor) {
        return valor.replace(/\D/g, '')
            .replace(/^(\d{2})(\d)/, '$1.$2')
            .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
            .replace(/\.(\d{3})(\d)/, '.$1/$2')
            .replace(/(\d{4})(\d)/, '$1-$2')
            .slice(0, 18);
    }
    function mascaraTelefone(valor) {
        valor = valor.replace(/\D/g, '').slice(0, 11);
        if (valor.length > 10) return valor.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
        if (valor.length > 6) return valor.replace(/^(\d{2})(\d{4})(\d{0,4})$/, '($1) $2-$3');
        if (valor.length > 2) return valor.replace(/^(\d{2})(\d{0,5})$/, '($1) $2');
        return valor;
    }
    function mascaraCEP(valor) {
        return valor.replace(/\D/g, '').slice(0, 8).replace(/^(\d{5})(\d)/, '$1-$2');
    }

    const tipoSelect = document.getElementById('tipo_pessoa');
    const cpfCnpjEl = document.getElementById('cpf_cnpj');
    const cepEl = document.getElementById('cep');
    const telefoneEl = document.getElementById('telefone');
    const celularEl = document.getElementById('celular');

    function aplicarMascaraCPFCNPJ() {
        const tipo = tipoSelect.value;
        if (tipo === 'F') {
            cpfCnpjEl.removeEventListener('input', aplicaCNPJ);
            cpfCnpjEl.addEventListener('input', aplicaCPF);
            cpfCnpjEl.maxLength = 14;
            cpfCnpjEl.placeholder = '000.000.000-00';
        } else {
            cpfCnpjEl.removeEventListener('input', aplicaCPF);
            cpfCnpjEl.addEventListener('input', aplicaCNPJ);
            cpfCnpjEl.maxLength = 18;
            cpfCnpjEl.placeholder = '00.000.000/0000-00';
        }
        // Atualiza máscara no valor atual
        if (tipo === 'F') {
            cpfCnpjEl.value = mascaraCPF(cpfCnpjEl.value);
        } else if (tipo === 'J') {
            cpfCnpjEl.value = mascaraCNPJ(cpfCnpjEl.value);
        }
    }
    function aplicaCPF() { this.value = mascaraCPF(this.value); }
    function aplicaCNPJ() { this.value = mascaraCNPJ(this.value); }

    tipoSelect.addEventListener('change', aplicarMascaraCPFCNPJ);
    aplicarMascaraCPFCNPJ();

    if (telefoneEl) telefoneEl.addEventListener('input', function() { this.value = mascaraTelefone(this.value); });
    if (celularEl) celularEl.addEventListener('input', function() { this.value = mascaraTelefone(this.value); });
    if (cepEl) cepEl.addEventListener('input', function() { this.value = mascaraCEP(this.value); });

    // ---------- EXISTENTE (mantido) ----------
    const btnBuscarCnpj = document.getElementById('btn-buscar-cnpj');
    const loadingCnpj = document.getElementById('loading-cnpj');
    const ieRgInput = document.getElementById('ie_rg');
    const textoIeRg = document.getElementById('texto-ie-rg');
    const labelNomeFantasia = document.getElementById('label-nome-fantasia');
    const nomeFantasiaInput = document.getElementById('nome_fantasia');
    const labelDataFundacao = document.getElementById('label-data-fundacao');
    const dataFundacaoInput = document.getElementById('data_fundacao');
    const btnBuscarCep = document.getElementById('btn-buscar-cep');
    const loadingCep = document.getElementById('loading-cep');

    function ajustarCamposPessoa() {
        const tipo = tipoSelect.value;
        if (tipo === 'F') {
            btnBuscarCnpj.style.display = 'none';
            textoIeRg.textContent = 'RG';
            ieRgInput.placeholder = 'RG (até 12 dígitos)';
            ieRgInput.maxLength = 12;
            labelNomeFantasia.style.display = 'none';
            nomeFantasiaInput.removeAttribute('required');
            labelDataFundacao.style.display = 'none';
            dataFundacaoInput.removeAttribute('required');
        } else if (tipo === 'J') {
            btnBuscarCnpj.style.display = 'inline-block';
            textoIeRg.textContent = 'Inscrição Estadual';
            ieRgInput.placeholder = 'Inscrição Estadual (até 14 dígitos)';
            ieRgInput.maxLength = 14;
            labelNomeFantasia.style.display = 'block';
            nomeFantasiaInput.setAttribute('required', 'required');
            labelDataFundacao.style.display = 'block';
            dataFundacaoInput.setAttribute('required', 'required');
        } else {
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
        let cnpj = cpfCnpjEl.value.replace(/\D/g, '');
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
        let cep = cepEl.value.replace(/\D/g, '');
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

<?php require __DIR__ . '/../../partials/dashboard_footer.php'; ?>