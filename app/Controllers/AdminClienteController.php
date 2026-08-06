<?php
/**
 * Arquivo: Controllers/AdminClienteController.php
 * Função: Controlador de edição de clientes pelo administrador.
 * 
 * Responsável por:
 *   - Permitir que o administrador edite qualquer cliente do sistema.
 *   - Alterar dados cadastrais do cliente (nome, CPF/CNPJ, endereço, contato, etc.).
 *   - Sincronizar os módulos contratados pelo cliente (privilégio exclusivo do admin).
 *   - Recalcular o valor total do cliente com base nos módulos selecionados
 *     e no salário mínimo vigente.
 *   - Renovar a licença do cliente automaticamente após a edição.
 * 
 * Diferente do ClienteController (usado pelo representante), este controlador
 * NÃO exige o representante_id nas operações, permitindo acesso irrestrito.
 * 
 * Acesso: Rotas protegidas pelo middleware AuthAdminMiddleware.
 */

require_once __DIR__ . '/../Models/Cliente.php';
require_once __DIR__ . '/../Models/ClienteModulo.php';
require_once __DIR__ . '/../Models/Modulo.php';
require_once __DIR__ . '/../Models/Licenca.php';
use Respect\Validation\Validator as v;

class AdminClienteController {
    
    /**
     * @var Cliente $model
     * Instância do Model Cliente para operações de banco de dados.
     */
    private Cliente $model;

    /**
     * Construtor da classe.
     * Inicializa o Model Cliente.
     */
    public function __construct() {
        $this->model = new Cliente();
    }

    // ============================================================
    // 1. EXIBIR FORMULÁRIO DE EDIÇÃO
    // ============================================================
    /**
     * Carrega o formulário de edição de cliente com todos os dados atuais.
     * Inclui a lista de módulos disponíveis e os já contratados pelo cliente.
     * 
     * @param int $id ID do cliente a ser editado.
     */
    public function editar(int $id): void {
        // Busca o cliente sem restrição de representante (admin)
        $cliente = $this->model->buscarPorIdAdmin($id);
        if (!$cliente) {
            echo "Cliente não encontrado.";
            exit;
        }
        
        // Obtém a lista de todos os módulos cadastrados
        $moduloModel = new Modulo();
        $modulos = $moduloModel->listarTodos();
        
        // Obtém os módulos já contratados por este cliente
        $cmModel = new ClienteModulo();
        $modulosCliente = $cmModel->getModulosDoCliente($id);
        $idsModulosCliente = array_column($modulosCliente, 'identificador');
        
        // Carrega a view de formulário (admin)
        require __DIR__ . '/../Views/admin/clientes/form.php';
    }

    // ============================================================
    // 2. PROCESSAR EDIÇÃO (SALVAR)
    // ============================================================
    /**
     * Processa o formulário de edição de cliente enviado pelo admin.
     * 
     * Fluxo:
     *   - Valida campos obrigatórios (tipo_pessoa, cpf_cnpj, nome, id).
     *   - Valida o CPF ou CNPJ usando a biblioteca Respect\Validation.
     *   - Atualiza os dados cadastrais do cliente.
     *   - Sincroniza os módulos contratados (admin pode alterar).
     *   - Recalcula e atualiza o valor total do cliente.
     *   - Renova a licença do cliente automaticamente.
     * 
     * Se campos obrigatórios estiverem vazios ou CPF/CNPJ inválido,
     * recarrega o formulário com uma mensagem de erro.
     */
    public function salvar(): void {
        // Verifica se a requisição é POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/licencas');
            exit;
        }

        // Captura os campos obrigatórios
        $tipo = $_POST['tipo_pessoa'] ?? null;
        $cpfCnpjRaw = $_POST['cpf_cnpj'] ?? null;
        $nome = $_POST['nome'] ?? null;
        $id = (int)($_POST['id'] ?? 0);

        // Se campos obrigatórios estiverem vazios, recarrega o formulário com erro
        if ($tipo === null || $cpfCnpjRaw === null || $nome === null || $id === 0) {
            $this->recarregarFormularioComErro($id, "Preencha todos os campos obrigatórios.");
            return;
        }

        // Remove caracteres não numéricos do CPF/CNPJ
        $cpfCnpj = preg_replace('/\D/', '', $cpfCnpjRaw);

        // Validação do CPF ou CNPJ conforme o tipo de pessoa
        try {
            if ($tipo === 'F') {
                v::cpf()->check($cpfCnpj);
            } else {
                v::cnpj()->check($cpfCnpj);
            }
        } catch (\Exception $e) {
            $this->recarregarFormularioComErro($id, "CPF/CNPJ inválido.");
            return;
        }

        // Monta o array de dados para atualização
        $dados = [
            ':tipo_pessoa' => $tipo,
            ':cpf_cnpj' => $cpfCnpj,
            ':ie_rg' => $_POST['ie_rg'] ?? null,
            ':nome' => $nome,
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

        // Atualiza os dados básicos do cliente (sem restrição de representante)
        $this->model->atualizarAdmin($id, $dados);

        // Sincroniza os módulos contratados (admin pode alterar livremente)
        $modulosSelecionados = $_POST['modulos'] ?? [];
        $cmModel = new ClienteModulo();
        $cmModel->sincronizar($id, $modulosSelecionados);

        // Recalcula o valor total com base nos módulos e salário mínimo
        $moduloModel = new Modulo();
        $valorTotal = $moduloModel->somaValores($modulosSelecionados);
        $this->model->atualizarValorTotal($id, $valorTotal);

        // Renova a licença do cliente automaticamente
        $licencaModel = new Licenca();
        $chave = $licencaModel->gerarChave();
        $licencaModel->criarOuAtualizar($id, $chave);

        // Redireciona para a lista de licenças do admin
        header('Location: /admin/licencas');
        exit;
    }

    // ============================================================
    // 3. RECARREGAR FORMULÁRIO COM ERRO
    // ============================================================
    /**
     * Reexibe o formulário de edição com uma mensagem de erro.
     * Utilizado quando a validação falha (campos vazios, CPF/CNPJ inválido).
     * 
     * @param int $id ID do cliente que estava sendo editado.
     * @param string $mensagem Mensagem de erro a ser exibida.
     */
    private function recarregarFormularioComErro(int $id, string $mensagem): void {
        $erro = $mensagem;
        $cliente = $this->model->buscarPorIdAdmin($id);
        $moduloModel = new Modulo();
        $modulos = $moduloModel->listarTodos();
        $cmModel = new ClienteModulo();
        $modulosCliente = $cmModel->getModulosDoCliente($id);
        $idsModulosCliente = array_column($modulosCliente, 'identificador');
        require __DIR__ . '/../Views/admin/clientes/form.php';
        exit;
    }
}