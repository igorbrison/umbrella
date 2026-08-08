<?php
/**
 * Arquivo: Controllers/SolicitacaoController.php
 * Função: Controlador de solicitações do representante.
 * 
 * Responsável por:
 *   - Listar, criar, editar e visualizar solicitações do representante.
 *   - A edição só é permitida se o status for "pendente".
 *   - A visualização inclui a resposta do administrador.
 */

require_once __DIR__ . '/../Models/Solicitacao.php';

class SolicitacaoController {
    private Solicitacao $model;
    private int $representanteId;

    public function __construct() {
        if (!isset($_SESSION['representante_id'])) {
            header('Location: /login');
            exit;
        }
        $this->model = new Solicitacao();
        $this->representanteId = $_SESSION['representante_id'];
    }

    // Listar e formulário de nova (com paginação)
    public function index(): void {
        $pagina = (int)($_GET['pagina'] ?? 1);
        $termo = $_GET['termo'] ?? '';
        $status = $_GET['status'] ?? '';
        $paginacao = $this->model->listarFiltradoPaginado($this->representanteId, $termo, $status, $pagina, 10);
        $solicitacoes = $paginacao['dados'];
        $sucesso = $_SESSION['sucesso_solicitacao'] ?? null;
        unset($_SESSION['sucesso_solicitacao']);
        require __DIR__ . '/../Views/painel/solicitacoes/index.php';
    }

    // Enviar nova
    public function enviar(): void {
        $titulo = $_POST['titulo'] ?? '';
        $descricao = $_POST['descricao'] ?? '';
        if (empty($titulo) || empty($descricao)) {
            echo "Preencha todos os campos.";
            exit;
        }
        $this->model->inserir($this->representanteId, $titulo, $descricao);
        $_SESSION['sucesso_solicitacao'] = 'Solicitação enviada com sucesso!';
        header('Location: /painel/solicitacoes');
        exit;
    }

    // Retorna dados de uma solicitação (JSON) para o modal de edição
    public function editar(int $id): void {
        $solicitacao = $this->model->buscarPorId($id);
        if (!$solicitacao || $solicitacao['representante_id'] != $this->representanteId) {
            http_response_code(404);
            echo json_encode(['erro' => 'Solicitação não encontrada.']);
            exit;
        }
        if ($solicitacao['status'] !== 'pendente') {
            http_response_code(403);
            echo json_encode(['erro' => 'Só é possível editar solicitações pendentes.']);
            exit;
        }
        echo json_encode($solicitacao);
    }

    // Processa a atualização da solicitação
    public function atualizar(): void {
        $id = (int)($_POST['id'] ?? 0);
        $titulo = $_POST['titulo'] ?? '';
        $descricao = $_POST['descricao'] ?? '';

        if (empty($titulo) || empty($descricao) || $id === 0) {
            echo "Preencha todos os campos.";
            exit;
        }

        $solicitacao = $this->model->buscarPorId($id);
        if (!$solicitacao || $solicitacao['representante_id'] != $this->representanteId) {
            echo "Solicitação não encontrada.";
            exit;
        }
        if ($solicitacao['status'] !== 'pendente') {
            echo "Só é possível editar solicitações pendentes.";
            exit;
        }

        // Atualiza título e descrição (status permanece pendente)
        $this->model->atualizar($id, $titulo, $descricao);
        $_SESSION['sucesso_solicitacao'] = 'Solicitação atualizada com sucesso!';
        header('Location: /painel/solicitacoes');
        exit;
    }

    // Visualizar detalhes da solicitação (retorna JSON)
    public function ver(int $id): void {
        $solicitacao = $this->model->buscarPorId($id);
        if (!$solicitacao || $solicitacao['representante_id'] != $this->representanteId) {
            http_response_code(404);
            echo json_encode(['erro' => 'Solicitação não encontrada.']);
            exit;
        }
        echo json_encode($solicitacao);
    }
}