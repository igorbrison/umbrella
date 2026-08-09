<?php
/**
 * Arquivo: Views/representantes/form.php
 * Função: VIEW de formulário para criação/edição de representantes (painel admin).
 * 
 * Permite ao administrador cadastrar um novo representante ou editar um existente.
 * Organizado em abas:
 *   - Aba 1: Dados da Empresa
 *   - Aba 2: Endereço
 *   - Aba 3: Contato e Observações
 */

// Inicializações seguras
if (!isset($representante) || !is_array($representante)) {
    $representante = [];
}

$modoEdicao = !empty($representante);
$titulo = $modoEdicao ? 'Editar Representante' : 'Novo Representante';

require __DIR__ . '/../partials/dashboard_header.php';
?>

<h1><?= $titulo ?></h1>

<form method="POST" action="/admin/representantes/salvar" id="form-representante">
    <input type="hidden" name="id" value="<?= $modoEdicao ? $representante['id'] : '' ?>">

    <div class="tabs-container">
        <div class="tabs-nav">
            <button type="button" class="tab-btn active" data-tab="tab-empresa">
                <i class="fas fa-building"></i> Dados da Empresa
            </button>
            <button type="button" class="tab-btn" data-tab="tab-endereco">
                <i class="fas fa-map-marker-alt"></i> Endereço
            </button>
            <button type="button" class="tab-btn" data-tab="tab-contato">
                <i class="fas fa-phone"></i> Contato
            </button>
        </div>

        <div class="tab-content">
            <!-- ===================== ABA 1: DADOS DA EMPRESA ===================== -->
            <div id="tab-empresa" class="tab-pane active">
                <fieldset>
                    <legend>Dados da Empresa</legend>
                    <div class="form-row">
                        <div class="form-col">
                            <label>CNPJ <span class="obrigatorio">*</span>:
                                <input type="text" name="cnpj" id="cpf_cnpj" required
                                       value="<?= $modoEdicao ? htmlspecialchars($representante['cnpj']) : '' ?>"
                                       maxlength="18" placeholder="00.000.000/0000-00">
                            </label>
                            <button type="button" id="btn-buscar-cnpj" class="btn-buscar">Buscar dados pelo CNPJ</button>
                            <span id="loading-cnpj" style="display:none;">Buscando...</span>
                        </div>
                        <div class="form-col">
                            <label>Inscrição Estadual <span class="obrigatorio">*</span>:
                                <input type="text" name="inscricao_estadual" id="rg_ie" required
                                       maxlength="14"
                                       value="<?= $modoEdicao ? htmlspecialchars($representante['inscricao_estadual']) : '' ?>"
                                       placeholder="Inscrição Estadual (até 14 dígitos)">
                                <span class="info">(preenchimento manual)</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <label>Razão Social <span class="obrigatorio">*</span>:
                                <input type="text" name="nome_razao" required
                                       value="<?= $modoEdicao ? htmlspecialchars($representante['nome_razao']) : '' ?>">
                            </label>
                        </div>
                        <div class="form-col">
                            <label>Nome Fantasia <span class="obrigatorio">*</span>:
                                <input type="text" name="nome_fantasia" required
                                       value="<?= $modoEdicao ? htmlspecialchars($representante['nome_fantasia']) : '' ?>">
                                <span class="info">(se não houver, repita a razão social)</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <label>Nome de Exibição (aparecerá no painel):
                                <input type="text" name="nome_exibicao"
                                       value="<?= $modoEdicao ? htmlspecialchars($representante['nome_exibicao'] ?? '') : '' ?>">
                                <span class="info">Se vazio, será usada a Razão Social.</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <label>CNAE <span class="obrigatorio">*</span>:
                                <input type="text" name="cnae" required
                                       value="<?= $modoEdicao ? htmlspecialchars($representante['cnae']) : '' ?>">
                            </label>
                        </div>
                        <div class="form-col">
                            <label>CRT <span class="obrigatorio">*</span>:
                                <select name="crt" required>
                                    <option value="">Selecione...</option>
                                    <option value="1" <?= ($modoEdicao && ($representante['crt'] ?? '') == '1') ? 'selected' : '' ?>>1 – Simples Nacional</option>
                                    <option value="2" <?= ($modoEdicao && ($representante['crt'] ?? '') == '2') ? 'selected' : '' ?>>2 – Simples Nacional (excesso de sublimite)</option>
                                    <option value="3" <?= ($modoEdicao && ($representante['crt'] ?? '') == '3') ? 'selected' : '' ?>>3 – Regime Normal</option>
                                    <option value="4" <?= ($modoEdicao && ($representante['crt'] ?? '') == '4') ? 'selected' : '' ?>>4 – MEI</option>
                                </select>
                                <span class="info">(preenchimento manual)</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <label>Data de Fundação <span class="obrigatorio">*</span>:
                                <input type="date" name="data_fundacao" id="data_nascimento" required
                                       value="<?= $modoEdicao ? ($representante['data_fundacao'] ?? '') : '' ?>">
                            </label>
                        </div>
                        <div class="form-col">
                            <label>Comissão (%) <span class="obrigatorio">*</span>:
                                <input type="number" step="0.01" name="comissao_percentual" required
                                       value="<?= $modoEdicao ? $representante['comissao_percentual'] : '' ?>">
                            </label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <label>Senha <?= !$modoEdicao ? '<span class="obrigatorio">*</span>' : '' ?>:
                                <input type="password" name="senha" id="senha" <?= $modoEdicao ? '' : 'required' ?>>
                                <?php if ($modoEdicao): ?>
                                    <small>Deixe em branco para manter a senha atual.</small>
                                <?php endif; ?>
                            </label>
                        </div>
                        <div class="form-col">
                            <label>Confirmar Senha <?= !$modoEdicao ? '<span class="obrigatorio">*</span>' : '' ?>:
                                <input type="password" name="confirmar_senha" id="confirmar_senha" <?= $modoEdicao ? '' : 'required' ?>>
                            </label>
                        </div>
                    </div>
                    <div id="erro-senha" style="color:red; display:none;">As senhas não conferem.</div>
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
                                       value="<?= $modoEdicao ? htmlspecialchars($representante['cep']) : '' ?>">
                            </label>
                            <button type="button" id="btn-buscar-cep" class="btn-buscar">Buscar endereço pelo CEP</button>
                            <span id="loading-cep" style="display:none;">Buscando...</span>
                        </div>
                        <div class="form-col">
                            <label>Logradouro <span class="obrigatorio">*</span>:
                                <input type="text" name="logradouro" required
                                       value="<?= $modoEdicao ? htmlspecialchars($representante['logradouro']) : '' ?>">
                            </label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <label>Número <span class="obrigatorio">*</span>:
                                <input type="text" name="numero" required
                                       value="<?= $modoEdicao ? htmlspecialchars($representante['numero']) : '' ?>">
                            </label>
                        </div>
                        <div class="form-col">
                            <label>Complemento:
                                <input type="text" name="complemento"
                                       value="<?= $modoEdicao ? htmlspecialchars($representante['complemento']) : '' ?>">
                            </label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <label>Bairro <span class="obrigatorio">*</span>:
                                <input type="text" name="bairro" required
                                       value="<?= $modoEdicao ? htmlspecialchars($representante['bairro']) : '' ?>">
                            </label>
                        </div>
                        <div class="form-col">
                            <label>Estado <span class="obrigatorio">*</span>:
                                <input type="text" name="estado" maxlength="2" required
                                       value="<?= $modoEdicao ? htmlspecialchars($representante['estado']) : '' ?>">
                            </label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <label>Município <span class="obrigatorio">*</span>:
                                <input type="text" name="municipio" required
                                       value="<?= $modoEdicao ? htmlspecialchars($representante['municipio']) : '' ?>">
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
                            <label>Telefone:
                                <input type="text" name="telefone"
                                       value="<?= $modoEdicao ? htmlspecialchars($representante['telefone']) : '' ?>">
                            </label>
                        </div>
                        <div class="form-col">
                            <label>Celular:
                                <input type="text" name="celular"
                                       value="<?= $modoEdicao ? htmlspecialchars($representante['celular']) : '' ?>">
                            </label>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-col">
                            <label>Email:
                                <input type="email" name="email"
                                       value="<?= $modoEdicao ? htmlspecialchars($representante['email']) : '' ?>">
                            </label>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Observações</legend>
                    <div class="form-row">
                        <div class="form-col">
                            <label>Observações:
                                <textarea name="observacoes" rows="4"><?= $modoEdicao ? htmlspecialchars($representante['observacoes']) : '' ?></textarea>
                            </label>
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>

    <!-- Botões de ação -->
    <div class="form-actions">
        <a href="/admin/representantes" class="btn btn-limpar">Cancelar</a>
        <button type="submit" class="btn-primary">Salvar</button>
    </div>
</form>

<!-- ===================== JAVASCRIPT ===================== -->
<script>
    const cpfCnpjInput = document.getElementById('cpf_cnpj');
    const btnBuscarCnpj = document.getElementById('btn-buscar-cnpj');
    const loadingCnpj = document.getElementById('loading-cnpj');
    const cepInput = document.getElementById('cep');
    const btnBuscarCep = document.getElementById('btn-buscar-cep');
    const loadingCep = document.getElementById('loading-cep');
    const erroSenha = document.getElementById('erro-senha');
    const senhaInput = document.getElementById('senha');
    const confirmarSenhaInput = document.getElementById('confirmar-senha');

    // Botão de buscar CNPJ sempre visível (representante é sempre pessoa jurídica)
    btnBuscarCnpj.style.display = 'inline-block';

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
                document.querySelector('input[name="nome_razao"]').value = data.nome || '';
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
                if (data.atividade_principal && data.atividade_principal.length > 0) {
                    document.querySelector('input[name="cnae"]').value = data.atividade_principal[0].code || '';
                }
                if (data.abertura) {
                    document.getElementById('data_nascimento').value = converterData(data.abertura);
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

    document.getElementById('form-representante').addEventListener('submit', function(e) {
        if (senhaInput.value !== confirmarSenhaInput.value) {
            e.preventDefault();
            erroSenha.style.display = 'block';
            return false;
        }
        erroSenha.style.display = 'none';
        return true;
    });
</script>

<?php require __DIR__ . '/../partials/dashboard_footer.php'; ?>