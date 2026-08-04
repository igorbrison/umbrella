<?php
require_once __DIR__ . '/../Models/Cliente.php';
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
        $ordenacaoAtual = ['coluna' => $ordem, 'direcao' => $direcao];
        require __DIR__ . '/../Views/painel/clientes/listar.php';
    }

    // Formulário de criação
    public function criar(): void {
        $cliente = [];
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

        if (!empty($_POST['id'])) {
            $id = (int)$_POST['id'];
            $this->model->atualizar($id, $this->representanteId, $dados);
        } else {
            $this->model->inserir($dados);
        }

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
        require __DIR__ . '/../Views/painel/clientes/form.php';
    }

    // Excluir cliente
    public function excluir(int $id): void {
        $this->model->excluir($id, $this->representanteId);
        header('Location: /painel/clientes');
        exit;
    }
}