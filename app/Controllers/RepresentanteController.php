<?php

/**
 * Arquivo: RepresentanteController.php
 * Função: CONTROLADOR da entidade "Representante".
 * 
 * Responsabilidades:
 *   - Receber as requisições HTTP vindas das rotas (GET, POST).
 *   - Validar os dados enviados pelo usuário (formulários).
 *   - Orquestrar as operações com o Model (banco de dados).
 *   - Carregar as Views corretas (listagem, formulários).
 * 
 * Fluxo típico: Rota -> Controller -> Model -> View -> Resposta.
 */

// 1. CARREGAMENTO DA CLASSE MODEL
// --------------------------------------------------------------
// Inclui a classe Representante (localizada em Models/).
// É ela quem contém os métodos que realmente acessam o banco de dados (SELECT, INSERT, UPDATE, DELETE).
require_once __DIR__ . '/../Models/Representante.php';

// 2. IMPORTAÇÃO DA BIBLIOTECA DE VALIDAÇÃO
// --------------------------------------------------------------
// Use a alias 'v' para a classe Validator da biblioteca Respect\Validation.
// Facilita a validação de dados com regras prontas, como CNPJ, e-mail, etc.
use Respect\Validation\Validator as v;

/**
 * Class RepresentanteController
 */
class RepresentanteController {

    /**
     * @var Representante $model 
     * Instância do Model que será usada para todas as operações de banco de dados.
     * Declarada com tipo privado para garantir encapsulamento.
     */
    private Representante $model;

    /**
     * Construtor da classe.
     * É executado automaticamente quando o Controller é instanciado.
     * Inicializa o Model Representante, deixando-o pronto para uso em todos os métodos.
     */
    public function __construct() {
        $this->model = new Representante();
    }

    /**
     * Método: index()
     * Rota associada: GET /admin/representantes
     * Função: Exibe a lista de todos os representantes cadastrados.
     * 
     * Características:
     *   - Permite ordenação dinâmica via Query String (ex: ?ordem=nome&direcao=desc).
     *   - Define valores padrão (ordenar por 'id' ascendente) caso os parâmetros não sejam enviados.
     *   - Repassa os dados da ordenação para a View, para que ela saiba qual coluna está ativa.
     */
    public function index(): void {
        // Captura os parâmetros de ordenação enviados pela URL (GET).
        // Se não existirem, usa 'id' e 'asc' como padrão.
        $ordem = $_GET['ordem'] ?? 'id';
        $direcao = $_GET['direcao'] ?? 'asc';

        // Busca os representantes no banco de dados através do Model,
        // aplicando a ordenação solicitada.
        $representantes = $this->model->listarComOrdenacao($ordem, $direcao);

        // Cria um array auxiliar para guardar a configuração atual da ordenação.
        // Isso será passado para a View para destacar a coluna pela qual a lista está ordenada.
        $ordenacaoAtual = [
            'coluna' => $ordem,
            'direcao' => $direcao
        ];

        // Inclui a View de listagem.
        // As variáveis $representantes e $ordenacaoAtual estarão disponíveis dentro do arquivo 'listar.php'.
        require __DIR__ . '/../Views/representantes/listar.php';
    }

    /**
     * Método: criar()
     * Rota associada: GET /admin/representantes/criar
     * Função: Exibe o formulário em branco para cadastrar um novo representante.
     * 
     * Observação: Apenas carrega a view do formulário. Como não há ID,
     * a view saberá que se trata de uma nova criação (campos vazios).
     */
    public function criar(): void {
        require __DIR__ . '/../Views/representantes/form.php';
    }

    /**
     * Método: salvar()
     * Rota associada: POST /admin/representantes/salvar
     * Função: Processa o envio do formulário (tanto para INSERIR quanto para ATUALIZAR).
     * 
     * Fluxo de decisão:
     *   1. Verifica se a requisição é POST (segurança).
     *   2. Valida se as senhas coincidem (quando preenchidas).
     *   3. Valida o CNPJ usando a biblioteca Respect/Validation.
     *   4. Monta um array com todos os campos do formulário.
     *   5. Se existir um 'id' no POST, chama o Model para ATUALIZAR (UPDATE).
     *   6. Senão, chama o Model para INSERIR (INSERT).
     *   7. Redireciona para a lista administrativa.
     */
    public function salvar(): void {
        // [SEGURANÇA] Impede que acessem este método via URL digitada (GET).
        // Se não for POST, redireciona para a lista.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/representantes');
            exit;
        }

        // [VALIDAÇÃO 1] Verifica se as senhas conferem.
        // Regra: Se a senha foi preenchida, ela deve ser igual à confirmação.
        if (!empty($_POST['senha']) && $_POST['senha'] !== ($_POST['confirmar_senha'] ?? '')) {
            echo "Erro: as senhas não conferem. <a href='javascript:history.back()'>Voltar</a>";
            exit;
        }

        // [VALIDAÇÃO 2] Validação do CNPJ.
        // Remove todos os caracteres não numéricos (pontos, barras, traços) para ficar apenas com os 14 dígitos.
        $cnpj = preg_replace('/\D/', '', $_POST['cnpj']);
        try {
            // A biblioteca Respect\Validation verifica se o CNPJ é matematicamente válido.
            v::cnpj()->check($cnpj);
        } catch (\Exception $e) {
            // Se a validação falhar, exibe erro e para a execução.
            echo "CNPJ inválido. <a href='javascript:history.back()'>Voltar</a>";
            exit;
        }

        // [MONTAGEM DOS DADOS]
        // Cria um array associativo onde as chaves têm ':' (preparadas para binding no PDO).
        // Usa o operador de coalescência nula (??) para evitar erros se o campo não existir no POST,
        // atribuindo null como padrão, o que é seguro para o banco de dados.
        $dados = [
            ':cnpj' => $cnpj,
            ':inscricao_estadual' => $_POST['inscricao_estadual'] ?? null,
            ':nome_razao' => $_POST['nome_razao'],
            ':nome_fantasia' => $_POST['nome_fantasia'] ?? null,
            ':nome_exibicao' => $_POST['nome_exibicao'] ?? null,
            ':cnae' => $_POST['cnae'] ?? null,
            ':crt' => $_POST['crt'] ?? null,
            ':data_fundacao' => !empty($_POST['data_fundacao']) ? $_POST['data_fundacao'] : null,
            ':comissao_percentual' => $_POST['comissao_percentual'] ?? null,
            ':logradouro' => $_POST['logradouro'] ?? null,
            ':numero' => $_POST['numero'] ?? null,
            ':complemento' => $_POST['complemento'] ?? null,
            ':bairro' => $_POST['bairro'] ?? null,
            ':cep' => $_POST['cep'] ?? null,
            ':estado' => $_POST['estado'] ?? null,
            ':municipio' => $_POST['municipio'] ?? null,
            ':telefone' => $_POST['telefone'] ?? null,
            ':celular' => $_POST['celular'] ?? null,
            ':email' => $_POST['email'] ?? null,
            ':observacoes' => $_POST['observacoes'] ?? null,
            ':ativo' => 1,
            ':senha' => $_POST['senha'] ?? ''
        ];

        // [DECISÃO: INSERIR OU ATUALIZAR?]
        if (!empty($_POST['id'])) {
            // --- CASO UPDATE (Edição) ---
            $id = (int)$_POST['id'];
            
            // Regra especial para edição: Se o campo 'senha' foi deixado em branco,
            // significa que o usuário NÃO quer alterar a senha atual.
            // Portanto, removemos o campo ':senha' do array para que o Model NÃO o atualize no banco,
            // mantendo a senha antiga intacta.
            if (empty($_POST['senha'])) {
                unset($dados[':senha']);
            }
            
            // Chama o Model para atualizar o registro específico.
            $this->model->atualizar($id, $dados);
        } else {
            // --- CASO INSERT (Novo cadastro) ---
            // Chama o Model para inserir um novo registro.
            $this->model->inserir($dados);
        }

        // Após salvar com sucesso, redireciona o navegador para a lista ADMINISTRATIVA.
        // O 'exit' garante que o script pare aqui, evitando execução de código indesejado.
        header('Location: /admin/representantes');
        exit;
    }

    /**
     * Método: editar(int $id)
     * Rota associada: GET /admin/representantes/editar/{id}
     * Função: Exibe o formulário PREENCHIDO com os dados do representante para edição.
     * 
     * @param int $id ID do representante a ser editado (vindo da URL).
     * 
     * Funcionamento:
     *   - Busca o representante no banco pelo ID.
     *   - Se não encontrar, exibe erro e interrompe.
     *   - Se encontrar, carrega a mesma View 'form.php'.
     *   - Atenção: A View 'form.php' recebe automaticamente a variável '$representante'
     *     (definida localmente aqui) e usa seus dados para preencher os campos do formulário.
     */
    public function editar(int $id): void {
        // Busca os dados do representante no banco.
        $representante = $this->model->buscarPorId($id);
        
        // Verifica se o registro existe.
        if (!$representante) {
            echo "Representante não encontrado.";
            exit;
        }
        
        // Inclui o formulário. A variável $representante está disponível na view.
        require __DIR__ . '/../Views/representantes/form.php';
    }

    /**
     * Método: status(int $id)
     * Rota associada: GET /admin/representantes/status/{id}
     * Função: Altera o status 'ativo' do representante (Toggle: inverte 0 para 1, ou 1 para 0).
     * 
     * @param int $id ID do representante.
     * 
     * Fluxo:
     *   1. Busca o representante para saber o status atual.
     *   2. Se encontrado, chama o Model para alterar o status.
     *      - Se ativo era 1, muda para 0 (inativa).
     *      - Se ativo era 0, muda para 1 (ativa).
     *   3. Redireciona para a lista administrativa.
     */
    public function status(int $id): void {
        // Busca o registro.
        $r = $this->model->buscarPorId($id);
        
        // Se encontrou, calcula o novo status (inverso do atual) e manda atualizar.
        if ($r) {
            $this->model->alterarStatus($id, $r['ativo'] ? 0 : 1);
        }
        
        // Redireciona de volta para a listagem administrativa.
        header('Location: /admin/representantes');
        exit;
    }

    /**
     * Método: excluir(int $id)
     * Rota associada: GET /admin/representantes/excluir/{id}
     * Função: Remove permanentemente o representante do banco de dados (DELETE).
     * 
     * @param int $id ID do representante a ser excluído.
     * 
     * Atenção: Normalmente, em sistemas reais, prefere-se o 'soft delete' (apenas marcar como inativo)
     * para não perder histórico. Este método executa uma exclusão física (hard delete).
     * Após excluir, redireciona para a lista administrativa.
     */
    public function excluir(int $id): void {
        // Chama o Model para deletar o registro.
        $this->model->excluir($id);
        
        // Redireciona para a lista administrativa.
        header('Location: /admin/representantes');
        exit;
    }
}