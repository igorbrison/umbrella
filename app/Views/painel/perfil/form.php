<?php
/**
 * Arquivo: Views/painel/perfil/form.php
 * Função: VIEW do formulário de edição do perfil do representante.
 * 
 * Organizado em abas para melhor usabilidade:
 *   - Aba 1: Dados Principais (CNPJ, IE, razão social, etc.)
 *   - Aba 2: Endereço
 *   - Aba 3: Contato e Observações
 * 
 * Mantém os recursos de busca de CNPJ e CEP.
 * Não permite alterar senha (feito no modal) nem status.
 */

// Garante que a variável $representante esteja sempre inicializada
if (!isset($representante) || !is_array($representante)) {
    $representante = [];
}

$titulo = 'Editar Meu Perfil';
require __DIR__ . '/../../partials/dashboard_header.php';

$sucessoPerfil = $_SESSION['sucesso_perfil'] ?? null;
$sucessoSenha   = $_SESSION['sucesso_senha'] ?? null;
$erroSenha      = $_SESSION['erro_senha'] ?? null;
unset($_SESSION['sucesso_perfil'], $_SESSION['sucesso_senha'], $_SESSION['erro_senha']);
?>

<h1><?= $titulo ?></h1>
<p class="subtitle">Atualize seus dados cadastrais abaixo</p>

<?php if ($sucessoPerfil): ?>
    <div class="mensagem sucesso"><?= htmlspecialchars($sucessoPerfil) ?></div>
<?php endif; ?>
<?php if ($sucessoSenha): ?>
    <div class="mensagem sucesso"><?= htmlspecialchars($sucessoSenha) ?></div>
<?php endif; ?>
<?php if ($erroSenha): ?>
    <div class="mensagem erro"><?= htmlspecialchars($erroSenha) ?></div>
<?php endif; ?>

<form method="POST" action="/painel/perfil/salvar" id="form-perfil">
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
        </div>

        <div class="tab-content">
            <!-- ABA 1: DADOS PRINCIPAIS (SOMENTE LEITURA) -->
            <div id="tab-principal" class="tab-pane active">
                <fieldset>
                    <legend>Dados da Empresa (não editáveis)</legend>
                    <div class="form-row">
                        <div class="form-col">
                            <label>CNPJ:</label>
                            <input type="text" name="cnpj" disabled
                                   value="<?= htmlspecialchars($representante['cnpj'] ?? '') ?>">
                        </div>
                        <div class="form-col">
                            <label>Inscrição Estadual:</label>
                            <input type="text" name="inscricao_estadual" disabled
                                   value="<?= htmlspecialchars($representante['inscricao_estadual'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <label>Razão Social:</label>
                            <input type="text" name="nome_razao" disabled
                                   value="<?= htmlspecialchars($representante['nome_razao'] ?? '') ?>">
                        </div>
                        <div class="form-col">
                            <label>Nome Fantasia:</label>
                            <input type="text" name="nome_fantasia" disabled
                                   value="<?= htmlspecialchars($representante['nome_fantasia'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <label>Nome de Exibição (aparecerá no painel):</label>
                            <input type="text" name="nome_exibicao"
                                   value="<?= htmlspecialchars($representante['nome_exibicao'] ?? '') ?>">
                        </div>
                        <div class="form-col">
                            <label>CNAE:</label>
                            <input type="text" name="cnae" disabled
                                   value="<?= htmlspecialchars($representante['cnae'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <label>CRT:</label>
                            <select name="crt" disabled>
                                <option value="1" <?= ($representante['crt'] ?? '') == '1' ? 'selected' : '' ?>>1 – Simples Nacional</option>
                                <option value="2" <?= ($representante['crt'] ?? '') == '2' ? 'selected' : '' ?>>2 – Simples Nacional (excesso de sublimite)</option>
                                <option value="3" <?= ($representante['crt'] ?? '') == '3' ? 'selected' : '' ?>>3 – Regime Normal</option>
                                <option value="4" <?= ($representante['crt'] ?? '') == '4' ? 'selected' : '' ?>>4 – MEI</option>
                            </select>
                        </div>
                        <div class="form-col">
                            <label>Data de Fundação:</label>
                            <input type="date" name="data_fundacao" disabled
                                   value="<?= $representante['data_fundacao'] ?? '' ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <label>Comissão (%):</label>
                            <input type="number" step="0.01" name="comissao_percentual" disabled
                                   value="<?= $representante['comissao_percentual'] ?? '' ?>">
                        </div>
                    </div>
                </fieldset>
            </div>

            <!-- ABA 2: ENDEREÇO (EDITÁVEL) -->
            <div id="tab-endereco" class="tab-pane">
                <fieldset>
                    <legend>Endereço</legend>
                    <div class="form-row">
                        <div class="form-col">
                            <label>CEP <span class="obrigatorio">*</span>:
                                <input type="text" name="cep" id="cep" maxlength="9" required
                                       value="<?= htmlspecialchars($representante['cep'] ?? '') ?>">
                            </label>
                            <button type="button" id="btn-buscar-cep" class="btn-buscar">Buscar endereço pelo CEP</button>
                            <span id="loading-cep" style="display:none;">Buscando...</span>
                        </div>
                        <div class="form-col">
                            <label>Logradouro <span class="obrigatorio">*</span>:
                                <input type="text" name="logradouro" required
                                       value="<?= htmlspecialchars($representante['logradouro'] ?? '') ?>">
                            </label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <label>Número <span class="obrigatorio">*</span>:
                                <input type="text" name="numero" required
                                       value="<?= htmlspecialchars($representante['numero'] ?? '') ?>">
                            </label>
                        </div>
                        <div class="form-col">
                            <label>Complemento:
                                <input type="text" name="complemento"
                                       value="<?= htmlspecialchars($representante['complemento'] ?? '') ?>">
                            </label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <label>Bairro <span class="obrigatorio">*</span>:
                                <input type="text" name="bairro" required
                                       value="<?= htmlspecialchars($representante['bairro'] ?? '') ?>">
                            </label>
                        </div>
                        <div class="form-col">
                            <label>Estado <span class="obrigatorio">*</span>:
                                <input type="text" name="estado" maxlength="2" required
                                       value="<?= htmlspecialchars($representante['estado'] ?? '') ?>">
                            </label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <label>Município <span class="obrigatorio">*</span>:
                                <input type="text" name="municipio" required
                                       value="<?= htmlspecialchars($representante['municipio'] ?? '') ?>">
                            </label>
                        </div>
                    </div>
                </fieldset>
            </div>

            <!-- ABA 3: CONTATO E OBSERVAÇÕES (EDITÁVEL) -->
            <div id="tab-contato" class="tab-pane">
                <fieldset>
                    <legend>Contato</legend>
                    <div class="form-row">
                        <div class="form-col">
                            <label>Telefone:
                                <input type="text" name="telefone"
                                       value="<?= htmlspecialchars($representante['telefone'] ?? '') ?>">
                            </label>
                        </div>
                        <div class="form-col">
                            <label>Celular:
                                <input type="text" name="celular"
                                       value="<?= htmlspecialchars($representante['celular'] ?? '') ?>">
                            </label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <label>Email:
                                <input type="email" name="email"
                                       value="<?= htmlspecialchars($representante['email'] ?? '') ?>">
                            </label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <label>Observações:
                                <textarea name="observacoes" rows="4"><?= htmlspecialchars($representante['observacoes'] ?? '') ?></textarea>
                            </label>
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="/painel/clientes" class="btn">Cancelar</a>
        <button type="submit" class="btn-primary">Salvar</button>
    </div>
</form>

<!-- Script apenas para busca de CEP -->
<script>
    const cepInput = document.getElementById('cep');
    const btnBuscarCep = document.getElementById('btn-buscar-cep');
    const loadingCep = document.getElementById('loading-cep');

    btnBuscarCep?.addEventListener('click', function() {
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

<?php require __DIR__ . '/../../partials/dashboard_footer.php'; ?>