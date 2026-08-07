<?php
/**
 * Arquivo: Views/painel/clientes/form.php
 * Função: VIEW de formulário para criação/edição de clientes (painel do representante).
 * 
 * Agora organizado em abas para melhor usabilidade:
 *   - Aba 1: Dados Principais (tipo pessoa, CPF/CNPJ, nome, etc.)
 *   - Aba 2: Endereço
 *   - Aba 3: Contato e Observações
 *   - Aba 4: Módulos (apenas na criação)
 * 
 * Mantém todos os recursos (busca CNPJ, CEP, validação de senha, etc.)
 */

// Garante que as variáveis estejam sempre inicializadas
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
$titulo = $modoEdicao ? 'Editar Cliente' : 'Novo Cliente';

// Inclui o cabeçalho do painel
require __DIR__ . '/../../partials/dashboard_header.php';
?>

<h1><?= $titulo ?></h1>
<p class="subtitle">Preencha os dados do cliente nos campos abaixo</p>

<!-- FORMULÁRIO DE CLIENTE -->
<form method="POST" action="/painel/clientes/salvar" id="form-cliente">
    <input type="hidden" name="id" value="<?= $modoEdicao ? $cliente['id'] : '' ?>">

    <div class="tabs-container">
        <!-- Navegação das abas -->
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

        <!-- Conteúdo das abas -->
        <div class="tab-content">
            <!-- ===================== ABA 1: DADOS PRINCIPAIS ===================== -->
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
                            <button type="button" id="btn-buscar-cnpj" style="display:none;">Buscar dados pelo CNPJ</button>
                            <span id="loading-cnpj" style="display:none;">Buscando...</span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <label id="label-ie-rg"><span id="texto-ie-rg">Inscrição Estadual</span> <span class="obrigatorio">*</span>:
                                <input type="text" name="ie_rg" id="ie_rg" required
                                       maxlength="14"
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

            <!-- ===================== ABA 2: ENDEREÇO ===================== -->
            <div id="tab-endereco" class="tab-pane">
                <fieldset>
                    <legend>Endereço</legend>
                    <div class="form-row">
                        <div class="form-col">
                            <label>CEP <span class="obrigatorio">*</span>:
                                <input type="text" name="cep" id="cep" maxlength="9" required
                                       value="<?= $modoEdicao ? htmlspecialchars($cliente['cep'] ?? '') : '' ?>">
                            </label>
                            <button type="button" id="btn-buscar-cep">Buscar endereço pelo CEP</button>
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

            <!-- ===================== ABA 3: CONTATO E OBSERVAÇÕES ===================== -->
            <div id="tab-contato" class="tab-pane">
                <fieldset>
                    <legend>Contato</legend>
                    <div class="form-row">
                        <div class="form-col">
                            <label>Telefone <span class="obrigatorio">*</span>:
                                <input type="text" name="telefone" required
                                       value="<?= $modoEdicao ? htmlspecialchars($cliente['telefone'] ?? '') : '' ?>">
                            </label>
                        </div>
                        <div class="form-col">
                            <label>Celular <span class="obrigatorio">*</span>:
                                <input type="text" name="celular" required
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
                            <label>Ativo:
                                <input type="checkbox" name="ativo" <?= (!$modoEdicao || ($cliente['ativo'] ?? 1)) ? 'checked' : '' ?>>
                            </label>
                        </div>
                    </div>
                </fieldset>
            </div>

            <!-- ===================== ABA 4: MÓDULOS CONTRATADOS ===================== -->
            <div id="tab-modulos" class="tab-pane">
                <fieldset>
                    <legend>Módulos Contratados</legend>
                    <?php if ($modoEdicao): ?>
                        <!-- Modo edição: exibe a lista de módulos como texto informativo -->
                        <p><em>Apenas o administrador pode alterar os módulos contratados.</em></p>
                        <?php if (!empty($idsModulosCliente)): ?>
                            <ul>
                                <?php foreach ($modulos as $m): ?>
                                    <?php if (in_array($m['identificador'], $idsModulosCliente)): ?>
                                        <li><?= htmlspecialchars($m['nome']) ?> (<?= htmlspecialchars($m['identificador']) ?>)</li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p>Nenhum módulo contratado.</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- Modo criação: exibe checkboxes para seleção -->
                        <?php foreach ($modulos as $m): ?>
                            <label>
                                <input type="checkbox" name="modulos[]" value="<?= $m['id'] ?>">
                                <?= htmlspecialchars($m['nome']) ?> (<?= htmlspecialchars($m['identificador']) ?>)
                            </label><br>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </fieldset>
            </div>
        </div>
    </div>

    <!-- Botões de ação -->
    <div class="form-actions">
        <a href="/painel/clientes" class="btn">Cancelar</a>
        <button type="submit" class="btn-primary">Salvar</button>
    </div>
</form>

<!-- ===================== JAVASCRIPT ===================== -->
<script>
    // Elementos do DOM
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

    /**
     * Ajusta a visibilidade e obrigatoriedade dos campos conforme o tipo de pessoa.
     */
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

    // Conversão de data dd/mm/aaaa para yyyy-mm-dd
    function converterData(dataStr) {
        if (!dataStr) return '';
        const partes = dataStr.split('/');
        if (partes.length !== 3) return '';
        return `${partes[2]}-${partes[1].padStart(2, '0')}-${partes[0].padStart(2, '0')}`;
    }

    // Busca CNPJ
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

    // Busca CEP
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

    // ============================================================
    // ABAS - JAVASCRIPT PARA ALTERNAR ENTRE AS ABAS
    // ============================================================
    document.addEventListener('DOMContentLoaded', function() {
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabPanes = document.querySelectorAll('.tab-pane');

        tabBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove classe active de todos os botões e painéis
                tabBtns.forEach(b => b.classList.remove('active'));
                tabPanes.forEach(p => p.classList.remove('active'));

                // Adiciona active ao botão clicado
                this.classList.add('active');

                // Mostra o painel correspondente
                const targetTab = this.getAttribute('data-tab');
                document.getElementById(targetTab).classList.add('active');
            });
        });
    });
</script>

<?php require __DIR__ . '/../../partials/dashboard_footer.php'; ?>