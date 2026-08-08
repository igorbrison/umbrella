<?php
/**
 * Arquivo: Controllers/AdminSolicitacaoController.php
 * Função: Controlador de gerenciamento de solicitações pelo administrador.
 * 
 * Responsável por:
 *   - Listar todas as solicitações enviadas pelos representantes.
 *   - Permitir que o administrador atualize o status e a resposta de cada solicitação.
 * 
 * Status disponíveis:
 *   - pendente          : Aguardando avaliação.
 *   - deferido          : Aprovado, mas ainda não iniciado.
 *   - indeferido        : Recusado.
 *   - em_desenvolvimento: Em andamento.
 *   - teste             : Em fase de testes.
 *   - concluido         : Finalizado.
 * 
 * Acesso: Rotas protegidas pelo middleware AuthAdminMiddleware.
 */

require_once __DIR__ . '/../Models/Solicitacao.php';

class AdminSolicitacaoController {
    
    /**
     * @var Solicitacao $model
     * Instância do Model Solicitacao para operações de banco de dados.
     */
    private Solicitacao $model;

    /**
     * Construtor da classe.
     * Inicializa o Model Solicitacao.
     */
    public function __construct() {
        $this->model = new Solicitacao();
    }

    // ============================================================
    // 1. LISTAR TODAS AS SOLICITAÇÕES
    // ============================================================
    /**
     * Exibe a lista de todas as solicitações de todos os representantes,
     * com opção de alterar o status e a resposta de cada uma.
     */
    public function index(): void {
        $solicitacoes = $this->model->listarTodas();
        require __DIR__ . '/../Views/admin/solicitacoes/listar.php';
    }

    // ============================================================
    // 2. ATUALIZAR STATUS DE UMA SOLICITAÇÃO (mantido para compatibilidade)
    // ============================================================
    /**
     * Processa a alteração de status de uma solicitação.
     * Recebe o ID da solicitação e o novo status via POST.
     * Redireciona de volta para a lista de solicitações.
     */
    public function atualizarStatus(): void {
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $this->model->atualizarStatus($id, $status);
        header('Location: /admin/solicitacoes');
        exit;
    }

    // ============================================================
    // 3. RESPONDER E ATUALIZAR STATUS DE UMA SOLICITAÇÃO
    // ============================================================
    /**
     * Processa a atualização de status e a resposta do administrador.
     * 
     * Recebe o ID, o novo status e a resposta via POST.
     * Atualiza ambos no banco de dados e redireciona para a lista.
     * 
     * Rota associada: POST /admin/solicitacoes/atualizar
     */
    public function responder(): void {
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $resposta = $_POST['resposta'] ?? '';

        if ($id <= 0 || empty($status)) {
            echo "Dados inválidos.";
            exit;
        }

        // Atualiza o status
        $this->model->atualizarStatus($id, $status);
        // Atualiza a resposta
        $this->model->responder($id, $resposta);

        header('Location: /admin/solicitacoes');
        exit;
    }
}