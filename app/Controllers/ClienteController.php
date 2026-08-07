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
 *   - A criação de cliente permite selecionar módulos e definir quantidade de máquinas.
 *   - A edição NÃO permite alterar os módulos nem a quantidade de máquinas (restrito ao admin).
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
    
    private Cliente $model;
    private int $representanteId;

    public function __construct() {
        if (!isset($_SESSION['representante_id'])) {
            header('Location: /login');
            exit;
        }
        $this->model = new Cliente();
        $this->representanteId = $_SESSION['representante_id'];
    }

    public function index(): void {
        $ordem = $_GET['ordem'] ?? 'id';
        $direcao = $_GET['direcao'] ?? 'asc';
        $clientes = $this->model->listarPorRepresentante($this->representanteId, $ordem, $direcao);

        foreach ($clientes as &$c) {
            $c['valor_total_atual'] = $this->model->getValorTotalAtual((int)$c['id']);
        }

        $licencaModel = new Licenca();
        foreach ($clientes as &$c) {
            $licenca = $licencaModel->buscarPorCliente((int)$c['id']);
            $c['data_expiracao'] = $licenca['data_expiracao'] ?? null;
            $c['licenca_ativa'] = $licenca['ativa'] ?? 0;
        }

        $ordenacaoAtual = ['coluna' => $ordem, 'direcao' => $direcao];
        require __DIR__ . '/../Views/painel/clientes/listar.php';
    }

    public function criar(): void {
        $cliente = [];
        $moduloModel = new Modulo();
        $modulos = $moduloModel->listarTodos();
        $idsModulosCliente = [];
        require __DIR__ . '/../Views/painel/clientes/form.php';
    }

    public function salvar(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /painel/clientes');
        exit;
    }

    $tipo = $_POST['tipo_pessoa'];
    $cpfCnpj = preg_replace('/\D/', '', $_POST['cpf_cnpj']);

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
        // ':ativo' removido – representante não gerencia status
    ];

    $modulosSelecionados = $_POST['modulos'] ?? [];
    $moduloModel = new Modulo();
    $valorTotal = $moduloModel->somaValores($modulosSelecionados);

    if (!empty($_POST['id'])) {
        // Edição: não altera módulos nem qtd_maquinas nem ativo
        $id = (int)$_POST['id'];
        unset($dados[':representante_id']);
        $this->model->atualizar($id, $this->representanteId, $dados);
    } else {
        // Criação: insere cliente, sincroniza módulos, define ativo=1
        $dados[':valor_total'] = $valorTotal;
        $dados[':ativo'] = 1;               // cliente sempre ativo ao cadastrar
        $this->model->inserir($dados);
        $id = (int) Database::getInstance()->lastInsertId();

        $cmModel = new ClienteModulo();
        $cmModel->sincronizar($id, $modulosSelecionados);

        $qtdMaquinas = (int)($_POST['qtd_maquinas'] ?? 1);
    }

    $licencaModel = new Licenca();
    $chave = $licencaModel->gerarChave();
    $licencaModel->criarOuAtualizar($id, $chave, $qtdMaquinas ?? 1);

    header('Location: /painel/clientes');
    exit;
}

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

    public function excluir(int $id): void {
        $this->model->excluir($id, $this->representanteId);
        header('Location: /painel/clientes');
        exit;
    }
}