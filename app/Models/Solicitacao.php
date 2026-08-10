<?php
/**
 * Arquivo: Models/Solicitacao.php
 * Função: Model da entidade "Solicitacao".
 * 
 * Responsável por:
 *   - Gerenciar solicitações enviadas pelos representantes.
 *   - Listar, criar, atualizar título/descrição, status e resposta.
 *   - Listagem paginada com filtros para o representante e para o admin.
 */

require_once __DIR__ . '/Database.php';

class Solicitacao {
    
    private \PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    // ==================== MÉTODOS BÁSICOS ====================

    /**
     * Busca uma solicitação pelo ID.
     */
    public function buscarPorId(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM solicitacoes WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Lista todas as solicitações de um representante (sem paginação).
     */
    public function listarPorRepresentante(int $representanteId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM solicitacoes WHERE representante_id = :rid ORDER BY criado_em DESC");
        $stmt->execute([':rid' => $representanteId]);
        return $stmt->fetchAll();
    }

    /**
     * Cria uma nova solicitação (alias para inserir).
     */
    public function criar(int $representanteId, string $titulo, string $descricao): int {
        return $this->inserir($representanteId, $titulo, $descricao);
    }

    /**
     * Insere uma nova solicitação.
     */
    public function inserir(int $representanteId, string $titulo, string $descricao): int {
        $stmt = $this->pdo->prepare("INSERT INTO solicitacoes (representante_id, titulo, descricao) VALUES (:rid, :titulo, :desc)");
        $stmt->execute([':rid' => $representanteId, ':titulo' => $titulo, ':desc' => $descricao]);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Atualiza título e descrição de uma solicitação pendente.
     */
    public function atualizar(int $id, string $titulo, string $descricao): void {
        $stmt = $this->pdo->prepare("UPDATE solicitacoes SET titulo = :titulo, descricao = :desc WHERE id = :id AND status = 'pendente'");
        $stmt->execute([':titulo' => $titulo, ':desc' => $descricao, ':id' => $id]);
    }

    /**
     * Atualiza apenas o status.
     */
    public function atualizarStatus(int $id, string $status): void {
        $stmt = $this->pdo->prepare("UPDATE solicitacoes SET status = :status WHERE id = :id");
        $stmt->execute([':status' => $status, ':id' => $id]);
    }

    /**
     * Atualiza a resposta do admin.
     */
    public function responder(int $id, string $resposta): void {
        $stmt = $this->pdo->prepare("UPDATE solicitacoes SET resposta = :resposta WHERE id = :id");
        $stmt->execute([':resposta' => $resposta, ':id' => $id]);
    }

    // ==================== LISTAGEM PAGINADA PARA O REPRESENTANTE ====================

    /**
     * Lista solicitações do representante com filtros e paginação.
     * 
     * @param int    $representanteId ID do representante.
     * @param string $termo           Busca por título.
     * @param string $status          Filtro de status.
     * @param int    $pagina          Número da página.
     * @param int    $limite          Registros por página.
     * @return array Com as chaves 'dados', 'total', 'pagina_atual', 'total_paginas'.
     */
    public function listarFiltradoPaginado(int $representanteId, string $termo = '', string $status = '', int $pagina = 1, int $limite = 10): array {
        $offset = ($pagina - 1) * $limite;
        $where = ["representante_id = :rid"];
        $params = [':rid' => $representanteId];

        if ($termo) {
            $where[] = "titulo LIKE :termo";
            $params[':termo'] = "%$termo%";
        }
        if ($status) {
            $where[] = "status = :status";
            $params[':status'] = $status;
        }

        $whereClause = implode(' AND ', $where);

        // Total de registros
        $sqlCount = "SELECT COUNT(*) FROM solicitacoes WHERE $whereClause";
        $stmt = $this->pdo->prepare($sqlCount);
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        // Dados da página
        $sql = "SELECT * FROM solicitacoes WHERE $whereClause ORDER BY criado_em DESC LIMIT $limite OFFSET $offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $dados = $stmt->fetchAll();

        return [
            'dados'          => $dados,
            'total'          => $total,
            'pagina_atual'   => $pagina,
            'total_paginas'  => $total > 0 ? (int)ceil($total / $limite) : 1,
        ];
    }

    // ==================== LISTAGEM PAGINADA PARA O ADMIN ====================

    /**
     * Lista todas as solicitações com paginação, filtro por status e busca por título.
     * (Já existia, mantido para compatibilidade com AdminSolicitacaoController)
     */
    public function listarTodasPaginado(int $pagina, int $limite, string $status = '', string $termo = ''): array {
        $offset = ($pagina - 1) * $limite;
        $where = [];
        $params = [];

        if ($status) {
            $where[] = "s.status = :status";
            $params[':status'] = $status;
        }
        if ($termo) {
            $where[] = "s.titulo LIKE :termo";
            $params[':termo'] = "%$termo%";
        }

        $whereClause = $where ? ' AND ' . implode(' AND ', $where) : '';

        $sql = "SELECT s.*, r.nome_razao AS representante_nome 
                FROM solicitacoes s
                JOIN representantes r ON r.id = s.representante_id
                WHERE 1=1 $whereClause
                ORDER BY s.criado_em DESC
                LIMIT $limite OFFSET $offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Conta o total de solicitações (para paginação) com filtros opcionais.
     */
    public function contarTodas(string $status = '', string $termo = ''): int {
        $where = [];
        $params = [];
        if ($status) {
            $where[] = "status = :status";
            $params[':status'] = $status;
        }
        if ($termo) {
            $where[] = "titulo LIKE :termo";
            $params[':termo'] = "%$termo%";
        }
        $whereClause = $where ? ' WHERE ' . implode(' AND ', $where) : '';
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM solicitacoes s $whereClause");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
}