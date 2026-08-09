<?php
/**
 * Arquivo: Controllers/AdminSolicitacaoController.php
 * Função: Controlador de gerenciamento de solicitações pelo administrador.
 * 
 * Responsável por:
 *   - Listar todas as solicitações enviadas pelos representantes com filtros e paginação.
 *   - Permitir que o administrador atualize o status e a resposta de cada solicitação.
 * 
 * Acesso: Rotas protegidas pelo middleware AuthAdminMiddleware.
 */

require_once __DIR__ . '/../Models/Solicitacao.php';

class AdminSolicitacaoController {
    
    private Solicitacao $model;

    public function __construct() {
        $this->model = new Solicitacao();
    }

    /**
     * Lista todas as solicitações com filtros e paginação.
     */
    public function index(): void {
        $pagina = (int)($_GET['pagina'] ?? 1);
        $status = $_GET['status'] ?? '';
        $termo = $_GET['termo'] ?? '';

        $solicitacoes = $this->model->listarTodasPaginado($pagina, 10, $status, $termo);
        $total = $this->model->contarTodas($status, $termo);
        $totalPaginas = ceil($total / 10);

        $paginacao = [
            'pagina_atual' => $pagina,
            'total_paginas' => $totalPaginas,
        ];

        require __DIR__ . '/../Views/admin/solicitacoes/listar.php';
    }

    /**
     * Atualiza status e resposta de uma solicitação.
     */
    public function responder(): void {
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $resposta = $_POST['resposta'] ?? '';

        if ($id <= 0 || empty($status)) {
            echo "Dados inválidos.";
            exit;
        }

        $this->model->atualizarStatus($id, $status);
        $this->model->responder($id, $resposta);

        header('Location: /admin/solicitacoes?sucesso=1');
        exit;
    }
}