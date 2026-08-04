<?php
/**
 * Arquivo: Views/representantes/form.php
 * Função: VIEW de FORMULÁRIO para criação/edição de representantes.
 * 
 * Este formulário é usado tanto para CADASTRAR novos representantes (via GET /criar)
 * quanto para EDITAR existentes (via GET /editar/{id}).
 * 
 * Comportamento:
 *   - Se a variável $representante estiver vazia, estamos no modo "Novo".
 *   - Se $representante tiver dados, estamos no modo "Edição".
 * 
 * Recursos adicionais:
 *   - Busca automática de dados do CNPJ (via API própria).
 *   - Busca automática de endereço pelo CEP (via API própria).
 *   - Validação de senha em JavaScript (confirmar senha).
 * 
 * Observação: A view NÃO faz validações no servidor; apenas exibe o formulário.
 * As validações de servidor estão no Controller (RepresentanteController@salvar).
 */

// 1. DETERMINA O MODO (CRIAÇÃO OU EDIÇÃO)
// --------------------------------------------------------------
// $representante é passado pelo controller:
//   - No método 'criar': $representante = [] (vazio).
//   - No método 'editar': $representante = ['id'=>1, 'nome'=>'...', ...].
if (!isset($representante) || !is_array($representante)) {
    $representante = []; // Fallback para array vazio caso não definido.
}

// Define o modo de edição: true se tiver dados (ID presente), false caso contrário.
$modoEdicao = !empty($representante);

// Título dinâmico conforme o modo.
$titulo = $modoEdicao ? 'Editar Representante' : 'Novo Representante';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= $titulo ?></title>
    <style>
        /* Estilos básicos para labels, grupos, campos obrigatórios e informações */
        label { display: block; margin-top: 10px; }
        .grupo { margin: 15px 0; }
        button[type="button"] { margin-left: 5px; }
        .obrigatorio { color: red; font-weight: bold; margin-left: 3px; }
        .info { font-size: 0.85em; color: #555; margin-left: 5px; }
    </style>
</head>
<body>
    <h1><?= $titulo ?></h1>
    
    <!-- FORMULÁRIO PRINCIPAL -->
    <!-- 
        O método é POST e a ação é '/representantes/salvar' 
        (rota que chama RepresentanteController@salvar).
    -->
    <form method="POST" action="/admin/representantes/salvar" id="form-representante">
        <!-- 
            Campo oculto 'id': usado para identificar se é edição.
            Se estiver vazio, o controller entenderá como INSERT (novo).
        -->
        <input type="hidden" name="id" value="<?= $modoEdicao ? $representante['id'] : '' ?>">

        <!-- ===================== SEÇÃO 1: DADOS DA EMPRESA ===================== -->
        <fieldset><legend>Dados da Empresa</legend>
            
            <!-- CNPJ -->
            <label>CNPJ <span class="obrigatorio">*</span>:
                <input type="text" name="cnpj" id="cpf_cnpj" required
                       value="<?= $modoEdicao ? htmlspecialchars($representante['cnpj']) : '' ?>"
                       maxlength="18" placeholder="00.000.000/0000-00">
                <!-- maxlength 18 permite formato com máscara (14 dígitos + pontuação) -->
            </label>
            <!-- Botão para buscar dados via CNPJ (consulta à ReceitaWS) -->
            <button type="button" id="btn-buscar-cnpj">Buscar dados pelo CNPJ</button>
            <!-- Indicador de carregamento (oculto inicialmente) -->
            <span id="loading-cnpj" style="display:none;">Buscando...</span>

            <!-- Inscrição Estadual (preenchimento manual, mesmo que a API possa trazer) -->
            <label>Inscrição Estadual <span class="obrigatorio">*</span>:
                <input type="text" name="inscricao_estadual" id="rg_ie" required
                       maxlength="14"
                       value="<?= $modoEdicao ? htmlspecialchars($representante['inscricao_estadual']) : '' ?>"
                       placeholder="Inscrição Estadual (até 14 dígitos)">
                <span class="info">(preenchimento manual)</span>
            </label>

            <!-- Razão Social (obrigatório) -->
            <label>Razão Social <span class="obrigatorio">*</span>:
                <input type="text" name="nome_razao" required
                       value="<?= $modoEdicao ? htmlspecialchars($representante['nome_razao']) : '' ?>">
            </label>

            <!-- Nome Fantasia (obrigatório, com dica para repetir razão social se não houver) -->
            <label>Nome Fantasia <span class="obrigatorio">*</span>:
                <input type="text" name="nome_fantasia" required
                       value="<?= $modoEdicao ? htmlspecialchars($representante['nome_fantasia']) : '' ?>">
                <span class="info">(se não houver, repita a razão social)</span>
            </label>

            <!-- CNAE (Classificação Nacional de Atividades Econômicas) -->
            <label>CNAE <span class="obrigatorio">*</span>:
                <input type="text" name="cnae" required
                       value="<?= $modoEdicao ? htmlspecialchars($representante['cnae']) : '' ?>">
            </label>

            <!-- CRT: Código de Regime Tributário -->
            <label>CRT <span class="obrigatorio">*</span>:
                <select name="crt" required>
                    <option value="">Selecione...</option>
                    <!-- Os valores numéricos correspondem aos códigos do SPED/EFD -->
                    <option value="1" <?= ($modoEdicao && ($representante['crt'] ?? '') == '1') ? 'selected' : '' ?>>1 – Simples Nacional</option>
                    <option value="2" <?= ($modoEdicao && ($representante['crt'] ?? '') == '2') ? 'selected' : '' ?>>2 – Simples Nacional (excesso de sublimite)</option>
                    <option value="3" <?= ($modoEdicao && ($representante['crt'] ?? '') == '3') ? 'selected' : '' ?>>3 – Regime Normal</option>
                    <option value="4" <?= ($modoEdicao && ($representante['crt'] ?? '') == '4') ? 'selected' : '' ?>>4 – MEI</option>
                </select>
                <span class="info">(preenchimento manual)</span>
            </label>

            <!-- Data de Fundação (campo tipo date) -->
            <label>Data de Fundação <span class="obrigatorio">*</span>:
                <input type="date" name="data_fundacao" id="data_nascimento" required
                       value="<?= $modoEdicao ? ($representante['data_fundacao'] ?? '') : '' ?>">
            </label>

            <!-- Comissão Percentual (número decimal com duas casas) -->
            <label>Comissão (%) <span class="obrigatorio">*</span>:
                <input type="number" step="0.01" name="comissao_percentual" required
                       value="<?= $modoEdicao ? $representante['comissao_percentual'] : '' ?>">
            </label>

            <!-- Campo Senha: 
                 - No modo "Novo": é obrigatório (required).
                 - No modo "Edição": não é obrigatório; se deixado em branco, a senha não é alterada.
            -->
            <label>Senha <?= !$modoEdicao ? '<span class="obrigatorio">*</span>' : '' ?>:
                <input type="password" name="senha" id="senha" <?= $modoEdicao ? '' : 'required' ?>>
                <?php if ($modoEdicao): ?>
                    <small>Deixe em branco para manter a senha atual.</small>
                <?php endif; ?>
            </label>

            <!-- Confirmar Senha (validação em JavaScript) -->
            <label>Confirmar Senha <?= !$modoEdicao ? '<span class="obrigatorio">*</span>' : '' ?>:
                <input type="password" name="confirmar_senha" id="confirmar_senha" <?= $modoEdicao ? '' : 'required' ?>>
            </label>
            <!-- Mensagem de erro (oculta) exibida caso as senhas não coincidam -->
            <div id="erro-senha" style="color:red; display:none;">As senhas não conferem.</div>

        </fieldset>

        <!-- ===================== SEÇÃO 2: ENDEREÇO ===================== -->
        <fieldset class="grupo"><legend>Endereço</legend>
            
            <!-- CEP -->
            <label>CEP <span class="obrigatorio">*</span>:
                <input type="text" name="cep" id="cep" maxlength="9" required
                       value="<?= $modoEdicao ? htmlspecialchars($representante['cep']) : '' ?>">
            </label>
            <!-- Botão para buscar endereço via CEP (ViaCEP) -->
            <button type="button" id="btn-buscar-cep">Buscar endereço pelo CEP</button>
            <!-- Indicador de carregamento -->
            <span id="loading-cep" style="display:none;">Buscando...</span>

            <!-- Campos de endereço preenchidos manualmente ou via API -->
            <label>Logradouro <span class="obrigatorio">*</span>:
                <input type="text" name="logradouro" required
                       value="<?= $modoEdicao ? htmlspecialchars($representante['logradouro']) : '' ?>">
            </label>
            <label>Número <span class="obrigatorio">*</span>:
                <input type="text" name="numero" required
                       value="<?= $modoEdicao ? htmlspecialchars($representante['numero']) : '' ?>">
            </label>
            <label>Complemento:
                <input type="text" name="complemento"
                       value="<?= $modoEdicao ? htmlspecialchars($representante['complemento']) : '' ?>">
            </label>
            <label>Bairro <span class="obrigatorio">*</span>:
                <input type="text" name="bairro" required
                       value="<?= $modoEdicao ? htmlspecialchars($representante['bairro']) : '' ?>">
            </label>
            <label>Estado <span class="obrigatorio">*</span>:
                <input type="text" name="estado" maxlength="2" required
                       value="<?= $modoEdicao ? htmlspecialchars($representante['estado']) : '' ?>">
            </label>
            <label>Município <span class="obrigatorio">*</span>:
                <input type="text" name="municipio" required
                       value="<?= $modoEdicao ? htmlspecialchars($representante['municipio']) : '' ?>">
            </label>

        </fieldset>

        <!-- ===================== SEÇÃO 3: CONTATO ===================== -->
        <fieldset class="grupo"><legend>Contato</legend>
            <!-- Telefone, celular e email são opcionais -->
            <label>Telefone:
                <input type="text" name="telefone"
                       value="<?= $modoEdicao ? htmlspecialchars($representante['telefone']) : '' ?>">
            </label>
            <label>Celular:
                <input type="text" name="celular"
                       value="<?= $modoEdicao ? htmlspecialchars($representante['celular']) : '' ?>">
            </label>
            <label>Email:
                <input type="email" name="email"
                       value="<?= $modoEdicao ? htmlspecialchars($representante['email']) : '' ?>">
            </label>
        </fieldset>

        <!-- ===================== SEÇÃO 4: OBSERVAÇÕES E STATUS ===================== -->
        <fieldset class="grupo">
            <label>Observações:
                <textarea name="observacoes" rows="4"><?= $modoEdicao ? htmlspecialchars($representante['observacoes']) : '' ?></textarea>
            </label>
            <label>Ativo:
                <!-- Checkbox: marcado por padrão (novo ou se ativo=1), desmarcado se ativo=0 -->
                <input type="checkbox" name="ativo" <?= (!$modoEdicao || ($representante['ativo'] ?? 1)) ? 'checked' : '' ?>>
            </label>
        </fieldset>

        <!-- Botões de ação -->
        <button type="submit">Salvar</button>
        <a href="/admin/representantes">Cancelar</a>
    </form>

    <!-- ===================== JAVASCRIPT ===================== -->
    <script>
        // Referências aos elementos do DOM usados nas funções
        const cpfCnpjInput = document.getElementById('cpf_cnpj');
        const btnBuscarCnpj = document.getElementById('btn-buscar-cnpj');
        const loadingCnpj = document.getElementById('loading-cnpj');
        const cepInput = document.getElementById('cep');
        const btnBuscarCep = document.getElementById('btn-buscar-cep');
        const loadingCep = document.getElementById('loading-cep');
        const erroSenha = document.getElementById('erro-senha');
        const senhaInput = document.getElementById('senha');
        const confirmarSenhaInput = document.getElementById('confirmar-senha');

        // Exibe o botão de busca CNPJ (já visível por padrão, mas reforça)
        btnBuscarCnpj.style.display = 'inline-block';

        /**
         * Função auxiliar: converte data no formato "dd/mm/aaaa" para "aaaa-mm-dd"
         * (formato aceito pelo input type="date").
         * @param {string} dataStr - Data no formato "dd/mm/aaaa"
         * @returns {string} Data no formato "aaaa-mm-dd" ou string vazia
         */
        function converterData(dataStr) {
            if (!dataStr) return '';
            const partes = dataStr.split('/');
            if (partes.length !== 3) return '';
            return `${partes[2]}-${partes[1].padStart(2, '0')}-${partes[0].padStart(2, '0')}`;
        }

        // ===== EVENTO DE BUSCA POR CNPJ =====
        btnBuscarCnpj.addEventListener('click', function() {
            // Remove caracteres não numéricos
            let cnpj = cpfCnpjInput.value.replace(/\D/g, '');
            // Verifica se tem 14 dígitos
            if (cnpj.length !== 14) {
                alert('Digite um CNPJ completo (14 números).');
                return;
            }
            // Exibe loading
            loadingCnpj.style.display = 'inline';
            // Faz requisição GET para o nosso proxy (consulta_cnpj.php)
            fetch('/api/buscar_cnpj.php?cnpj=' + cnpj)
                .then(response => response.json())
                .then(data => {
                    loadingCnpj.style.display = 'none';
                    console.log('Dados recebidos:', data);
                    // Se a API retornou erro (ou CNPJ inválido/não encontrado)
                    if (data.status === 'ERROR') {
                        alert(data.message || 'CNPJ não encontrado ou inválido.');
                        return;
                    }
                    // Preenche os campos do formulário com os dados retornados
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
                    // Preenche o CNAE (primeira atividade principal)
                    if (data.atividade_principal && data.atividade_principal.length > 0) {
                        document.querySelector('input[name="cnae"]').value = data.atividade_principal[0].code || '';
                    }
                    // Preenche a data de fundação (converte formato)
                    if (data.abertura) {
                        document.getElementById('data_nascimento').value = converterData(data.abertura);
                    }
                })
                .catch(error => {
                    loadingCnpj.style.display = 'none';
                    alert('Erro ao buscar CNPJ. Tente novamente.');
                });
        });

        // ===== EVENTO DE BUSCA POR CEP =====
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
                    // A API ViaCEP retorna {erro: true} se não encontrar
                    if (data.erro) {
                        alert('CEP não encontrado.');
                        return;
                    }
                    // Preenche os campos de endereço
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

        // ===== VALIDAÇÃO DE SENHA ANTES DO ENVIO =====
        document.getElementById('form-representante').addEventListener('submit', function(e) {
            // Compara os campos senha e confirmar senha
            if (senhaInput.value !== confirmarSenhaInput.value) {
                // Impede o envio do formulário
                e.preventDefault();
                // Exibe a mensagem de erro
                erroSenha.style.display = 'block';
                return false;
            }
            // Se estão iguais, esconde a mensagem de erro (caso estivesse visível)
            erroSenha.style.display = 'none';
            return true;
        });
    </script>
</body>
</html>