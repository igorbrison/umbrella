<?php
/**
 * Arquivo: Models/Solicitacao.php
 * Função: Model da entidade "Solicitacao".
 * 
 * Responsável por:
 *   - Gerenciar solicitações enviadas pelos representantes.
 *   - Listar, criar, atualizar status e resposta.
 *   - Listagem paginada com filtros para o admin.
 */

require_once __DIR__ . '/Database.php';

class Solicitacao {
    
    private \PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    // ==================== MÉTODOS EXISTENTES (mantidos) ====================

    public function listarPorRepresentante(int $representanteId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM solicitacoes WHERE representante_id = :rid ORDER BY criado_em DESC");
        $stmt->execute([':rid' => $representanteId]);
        return $stmt->fetchAll();
    }

    public function criar(int $representanteId, string $titulo, string $descricao): int {
        $stmt = $this->pdo->prepare("INSERT INTO solicitacoes (representante_id, titulo, descricao) VALUES (:rid, :titulo, :desc)");
        $stmt->execute([':rid' => $representanteId, ':titulo' => $titulo, ':desc' => $descricao]);
        return (int)$this->pdo->lastInsertId();
    }

    public function atualizarStatus(int $id, string $status): void {
        $stmt = $this->pdo->prepare("UPDATE solicitacoes SET status = :status WHERE id = :id");
        $stmt->execute([':status' => $status, ':id' => $id]);
    }

    public function responder(int $id, string $resposta): void {
        $stmt = $this->pdo->prepare("UPDATE solicitacoes SET resposta = :resposta WHERE id = :id");
        $stmt->execute([':resposta' => $resposta, ':id' => $id]);
    }

    // ==================== NOVOS MÉTODOS PARA O ADMIN ====================

    /**
     * Lista todas as solicitações com paginação, filtro por status e busca por título.
     * 
     * @param int    $pagina Número da página.
     * @param int    $limite Registros por página.
     * @param string $status Filtro opcional de status.
     * @param string $termo  Busca opcional por título.
     * @return array Lista de solicitações com nome do representante.
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
     * 
     * @param string $status Filtro de status.
     * @param string $termo  Busca por título.
     * @return int Número total de solicitações.
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