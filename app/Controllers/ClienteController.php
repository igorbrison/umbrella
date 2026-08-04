<?php
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

    // Listagem dos clientes do representante logado
    public function index(): void {
        $ordem = $_GET['ordem'] ?? 'id';
        $direcao = $_GET['direcao'] ?? 'asc';
        $clientes = $this->model->listarPorRepresentante($this->representanteId, $ordem, $direcao);

        // Adiciona o valor total atualizado (dinâmico) a cada cliente
        foreach ($clientes as &$c) {
            $c['valor_total_atual'] = $this->model->getValorTotalAtual((int)$c['id']);
        }

        $ordenacaoAtual = ['coluna' => $ordem, 'direcao' => $direcao];
        require __DIR__ . '/../Views/painel/clientes/listar.php';
    }

    // Formulário de criação
    public function criar(): void {
        $cliente = [];
        $moduloModel = new Modulo();
        $modulos = $moduloModel->listarTodos();
        $idsModulosCliente = []; // IDs dos módulos já selecionados (vazio)
        require __DIR__ . '/../Views/painel/clientes/form.php';
    }

    // Processa o formulário (criação ou edição)
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

    // Formulário de edição
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

    // Excluir cliente
    public function excluir(int $id): void {
        $this->model->excluir($id, $this->representanteId);
        header('Location: /painel/clientes');
        exit;
    }
}