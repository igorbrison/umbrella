<?php
/**
 * Arquivo: Controllers/ClienteController.php
 * Função: Controlador de gestão de clientes (painel do representante).
 * 
 * Responsável por:
 *   - Listar os clientes vinculados ao representante logado.
 *   - Exibir formulários de criação e edição de clientes.
 *   - Processar o cadastro de novos clientes (com módulos e valor total).
 *   - Processar a edição de clientes existentes (sem alterar módulos).
 *   - Excluir clientes do representante.
 *   - Gerar/renovar a licença automaticamente ao cadastrar um cliente.
 * 
 * Regras de negócio:
 *   - O representante só vê e gerencia seus próprios clientes.
 *   - A criação de cliente permite selecionar módulos.
 *   - A edição NÃO permite alterar os módulos (restrito ao admin).
 *   - O valor total é calculado com base nos módulos e salário mínimo.
 * 
 * Acesso: Rotas protegidas pelo middleware inline no grupo /painel.
 */

require_once __DIR__ . '/../Models/Cliente.php';
require_once __DIR__ . '/../Models/ClienteModulo.php';
require_once __DIR__ . '/../Models/Modulo.php';
require_once __DIR__ . '/../Models/Licenca.php';
require_once __DIR__ . '/../Models/Database.php'; // para lastInsertId
use Respect\Validation\Validator as v;

class ClienteController {
    
    /**
     * @var Cliente $model
     * Instância do Model Cliente para operações de banco de dados.
     */
    private Cliente $model;
    
    /**
     * @var int $representanteId
     * ID do representante logado (obtido da sessão).
     */
    private int $representanteId;

    /**
     * Construtor da classe.
     * Verifica a autenticação do representante e inicializa o Model.
     * Redireciona para /login se não houver sessão ativa.
     */
    public function __construct() {
        if (!isset($_SESSION['representante_id'])) {
            header('Location: /login');
            exit;
        }
        $this->model = new Cliente();
        $this->representanteId = $_SESSION['representante_id'];
    }

    // ============================================================
    // 1. LISTAR CLIENTES
    // ============================================================
    /**
     * Lista todos os clientes do representante logado.
     * Inclui o valor total atualizado (calculado dinamicamente com base
     * no salário mínimo vigente e nos módulos contratados).
     * 
     * Suporta ordenação por coluna clicável (id, nome, cpf_cnpj, email, ativo).
     */
    public function index(): void {
        $ordem = $_GET['ordem'] ?? 'id';
        $direcao = $_GET['direcao'] ?? 'asc';
        
        // Busca os clientes do representante
        $clientes = $this->model->listarPorRepresentante($this->representanteId, $ordem, $direcao);

        // Adiciona o valor total atualizado (dinâmico) a cada cliente
        foreach ($clientes as &$c) {
            $c['valor_total_atual'] = $this->model->getValorTotalAtual((int)$c['id']);
        }

        $ordenacaoAtual = ['coluna' => $ordem, 'direcao' => $direcao];
        require __DIR__ . '/../Views/painel/clientes/listar.php';
    }

    // ============================================================
    // 2. EXIBIR FORMULÁRIO DE CRIAÇÃO
    // ============================================================
    /**
     * Exibe o formulário em branco para cadastrar um novo cliente.
     * Carrega a lista de todos os módulos disponíveis para seleção.
     */
    public function criar(): void {
        $cliente = [];
        $moduloModel = new Modulo();
        $modulos = $moduloModel->listarTodos();
        $idsModulosCliente = []; // IDs dos módulos já selecionados (vazio)
        require __DIR__ . '/../Views/painel/clientes/form.php';
    }

    // ============================================================
    // 3. PROCESSAR FORMULÁRIO (CRIAÇÃO OU EDIÇÃO)
    // ============================================================
    /**
     * Processa o envio do formulário de cliente.
     * 
     * Fluxo:
     *   - Valida o CPF ou CNPJ usando a biblioteca Respect\Validation.
     *   - Se for EDIÇÃO (id presente):
     *       • Atualiza os dados básicos do cliente.
     *       • NÃO sincroniza módulos (representante não pode alterar).
     *   - Se for CRIAÇÃO (sem id):
     *       • Insere o novo cliente com o valor total calculado.
     *       • Sincroniza os módulos selecionados.
     *   - Gera/renova a licença automaticamente (expiração no próximo dia 5).
     */
    public function salvar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /painel/clientes');
            exit;
        }

        $tipo = $_POST['tipo_pessoa'];
        $cpfCnpj = preg_replace('/\D/', '', $_POST['cpf_cnpj']);

        // Validação do CPF ou CNPJ
        try {
            if ($tipo === 'F') {
                v::cpf()->check($cpfCnpj);
            } else {
                v::cnpj()->check($cpfCnpj);
            }
        } catch (\Exception $e) {
            echo "Erro: " . ($tipo === 'F' ? 'CPF' : 'CNPJ') . " inválido. <a href='javascript:history.back()'>Voltar</a>";
            exit;
        }

        // Monta o array de dados do cliente
        $dados = [
            ':representante_id' => $this->representanteId,
            ':tipo_pessoa' => $tipo,
            ':cpf_cnpj' => $cpfCnpj,
            ':ie_rg' => $_POST['ie_rg'] ?? null,
            ':nome' => $_POST['nome'],
            ':nome_fantasia' => $_POST['nome_fantasia'] ?? null,
            ':data_fundacao' => !empty($_POST['data_fundacao']) ? $_POST['data_fundacao'] : null,
            ':telefone' => $_POST['telefone'] ?? null,
            ':celular' => $_POST['celular'] ?? null,
            ':email' => $_POST['email'] ?? null,
            ':logradouro' => $_POST['logradouro'] ?? null,
            ':numero' => $_POST['numero'] ?? null,
            ':complemento' => $_POST['complemento'] ?? null,
            ':bairro' => $_POST['bairro'] ?? null,
            ':cep' => $_POST['cep'] ?? null,
            ':estado' => $_POST['estado'] ?? null,
            ':municipio' => $_POST['municipio'] ?? null,
            ':observacoes' => $_POST['observacoes'] ?? null,
            ':ativo' => isset($_POST['ativo']) ? 1 : 0
        ];

        // Calcula o valor total dos módulos selecionados (apenas na criação)
        $modulosSelecionados = $_POST['modulos'] ?? [];
        $moduloModel = new Modulo();
        $valorTotal = $moduloModel->somaValores($modulosSelecionados);

        if (!empty($_POST['id'])) {
            // --- EDIÇÃO: NÃO sincroniza módulos (representante não pode alterar) ---
            $id = (int)$_POST['id'];
            unset($dados[':representante_id']);
            $this->model->atualizar($id, $this->representanteId, $dados);
            // O valor total também não é alterado na edição, pois os módulos permanecem os mesmos.
        } else {
            // --- NOVO CADASTRO: sincroniza módulos e insere cliente ---
            $dados[':valor_total'] = $valorTotal;
            $this->model->inserir($dados);
            $id = (int) Database::getInstance()->lastInsertId();

            // Sincroniza os módulos selecionados para o cliente
            $cmModel = new ClienteModulo();
            $cmModel->sincronizar($id, $modulosSelecionados);
        }

        // Gera/renova licença (expiração automática no próximo dia 5)
        $licencaModel = new Licenca();
        $chave = $licencaModel->gerarChave();
        $licencaModel->criarOuAtualizar($id, $chave);

        header('Location: /painel/clientes');
        exit;
    }

    // ============================================================
    // 4. EXIBIR FORMULÁRIO DE EDIÇÃO
    // ============================================================
    /**
     * Carrega o formulário de edição com os dados atuais do cliente.
     * Exibe os módulos já contratados de forma somente leitura
     * (o representante não pode alterá-los).
     * 
     * @param int $id ID do cliente a ser editado.
     */
    public function editar(int $id): void {
        $cliente = $this->model->buscarPorId($id, $this->representanteId);
        if (!$cliente) {
            echo "Cliente não encontrado.";
            exit;
        }
        $moduloModel = new Modulo();
        $modulos = $moduloModel->listarTodos();
        $cmModel = new ClienteModulo();
        $modulosCliente = $cmModel->getModulosDoCliente($id);
        $idsModulosCliente = array_column($modulosCliente, 'identificador');
        require __DIR__ . '/../Views/painel/clientes/form.php';
    }

    // ============================================================
    // 5. EXCLUIR CLIENTE
    // ============================================================
    /**
     * Exclui permanentemente um cliente do representante (hard delete).
     * 
     * @param int $id ID do cliente a ser excluído.
     * 
     * ATENÇÃO: Esta operação é irreversível. Em sistemas críticos, considere
     * usar 'soft delete' (apenas marcar como inativo) em vez de exclusão física.
     */
    public function excluir(int $id): void {
        $this->model->excluir($id, $this->representanteId);
        header('Location: /painel/clientes');
        exit;
    }
}