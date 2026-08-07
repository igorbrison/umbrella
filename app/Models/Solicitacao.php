<?php
/**
 * Arquivo: Models/Solicitacao.php
 * Função: Model da entidade "Solicitacao".
 * 
 * Responsável por:
 *   - Gerenciar as solicitações feitas pelos representantes.
 *   - Listar solicitações por representante (painel do representante), com ou sem paginação.
 *   - Listar todas as solicitações (painel do administrador).
 *   - Inserir novas solicitações e atualizar o status delas.
 * 
 * Fluxo:
 *   1. Representante envia uma solicitação (título + descrição).
 *   2. Administrador visualiza todas as solicitações em uma lista.
 *   3. Administrador atualiza o status (pendente, deferido, indeferido,
 *      em desenvolvimento, teste, concluído).
 * 
 * Conexão: Utiliza o Singleton Database para obter uma instância PDO única.
 */

require_once __DIR__ . '/Database.php';

class Solicitacao {
    
    /**
     * @var \PDO $pdo
     * Instância do PDO para executar consultas SQL.
     */
    private \PDO $pdo;

    /**
     * Construtor da classe.
     * Obtém a instância única do banco de dados via Database::getInstance().
     */
    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    /**
     * Lista as solicitações de um representante específico.
     * Ordenadas da mais recente para a mais antiga.
     * 
     * @param int $representanteId ID do representante.
     * @return array Lista de solicitações.
     */
    public function listarPorRepresentante(int $representanteId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM solicitacoes WHERE representante_id = :rid ORDER BY criado_em DESC");
        $stmt->execute([':rid' => $representanteId]);
        return $stmt->fetchAll();
    }

    /**
     * Lista as solicitações de um representante com paginação.
     * 
     * @param int $representanteId ID do representante.
     * @param int $pagina Número da página (começa em 1).
     * @param int $limite Quantidade de registros por página (padrão 10).
     * @return array Array com chaves: dados, total, pagina_atual, total_paginas.
     */
    public function listarPaginadoPorRepresentante(int $representanteId, int $pagina = 1, int $limite = 10): array {
        // Conta o total de registros para calcular as páginas
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM solicitacoes WHERE representante_id = :rid");
        $stmt->execute([':rid' => $representanteId]);
        $total = (int)$stmt->fetchColumn();

        $totalPaginas = $total > 0 ? (int)ceil($total / $limite) : 1;
        $offset = ($pagina - 1) * $limite;

        $stmt = $this->pdo->prepare(
            "SELECT * FROM solicitacoes 
             WHERE representante_id = :rid 
             ORDER BY criado_em DESC 
             LIMIT :limite OFFSET :offset"
        );
        $stmt->bindValue(':rid', $representanteId, \PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limite, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return [
            'dados' => $stmt->fetchAll(),
            'total' => $total,
            'pagina_atual' => $pagina,
            'total_paginas' => $totalPaginas
        ];
    }

    /**
     * Lista todas as solicitações do sistema (para o administrador).
     * Inclui o nome do representante que fez cada solicitação.
     * 
     * @return array Lista de todas as solicitações.
     */
    public function listarTodas(): array {
        $stmt = $this->pdo->query(
            "SELECT s.*, r.nome_razao as representante_nome 
             FROM solicitacoes s 
             JOIN representantes r ON s.representante_id = r.id 
             ORDER BY s.criado_em DESC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Busca uma solicitação pelo ID.
     * 
     * @param int $id ID da solicitação.
     * @return array|null Dados da solicitação ou null se não encontrada.
     */
    public function buscarPorId(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM solicitacoes WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Insere uma nova solicitação no banco de dados.
     * O status inicial é sempre 'pendente' (definido pelo banco).
     * 
     * @param int $representanteId ID do representante que está solicitando.
     * @param string $titulo Título da solicitação.
     * @param string $descricao Descrição detalhada da solicitação.
     * @return bool True se a inserção for bem-sucedida.
     */
    public function inserir(int $representanteId, string $titulo, string $descricao): bool {
        $stmt = $this->pdo->prepare(
            "INSERT INTO solicitacoes (representante_id, titulo, descricao) 
             VALUES (:rid, :titulo, :desc)"
        );
        return $stmt->execute([
            ':rid' => $representanteId, 
            ':titulo' => $titulo, 
            ':desc' => $descricao
        ]);
    }

    /**
     * Atualiza o status de uma solicitação.
     * Utilizado pelo administrador para gerenciar as solicitações.
     * 
     * @param int $id ID da solicitação.
     * @param string $status Novo status (pendente, deferido, indeferido,
     *                        em_desenvolvimento, teste, concluido).
     * @return bool True se a atualização for bem-sucedida.
     */
    public function atualizarStatus(int $id, string $status): bool {
        $stmt = $this->pdo->prepare("UPDATE solicitacoes SET status = :status WHERE id = :id");
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    /**
     * Atualiza o título e a descrição de uma solicitação (mantém o status atual).
     * Utilizado pelo representante para corrigir solicitações pendentes.
     * 
     * @param int $id ID da solicitação.
     * @param string $titulo Novo título.
     * @param string $descricao Nova descrição.
     * @return bool True se a atualização for bem-sucedida.
     */
    public function atualizar(int $id, string $titulo, string $descricao): bool {
        $stmt = $this->pdo->prepare("UPDATE solicitacoes SET titulo = :titulo, descricao = :descricao WHERE id = :id");
        return $stmt->execute([':titulo' => $titulo, ':descricao' => $descricao, ':id' => $id]);
    }

    public function listarFiltradoPaginado(int $representanteId, string $termo = '', string $status = '', int $pagina = 1, int $limite = 10): array {
    $sql = "SELECT COUNT(*) FROM solicitacoes WHERE representante_id = :rid";
    $params = [':rid' => $representanteId];

    if (!empty($termo)) {
        $sql .= " AND (titulo LIKE :termo OR descricao LIKE :termo)";
        $params[':termo'] = '%' . $termo . '%';
    }
    if (!empty($status)) {
        $sql .= " AND status = :status";
        $params[':status'] = $status;
    }

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();

    $totalPaginas = $total > 0 ? (int)ceil($total / $limite) : 1;
    $offset = ($pagina - 1) * $limite;

    $sqlData = "SELECT * FROM solicitacoes WHERE representante_id = :rid";
    if (!empty($termo)) {
        $sqlData .= " AND (titulo LIKE :termo OR descricao LIKE :termo)";
    }
    if (!empty($status)) {
        $sqlData .= " AND status = :status";
    }
    $sqlData .= " ORDER BY criado_em DESC LIMIT :limite OFFSET :offset";

    $stmt = $this->pdo->prepare($sqlData);
    $stmt->bindValue(':rid', $representanteId, \PDO::PARAM_INT);
    if (!empty($termo)) $stmt->bindValue(':termo', '%' . $termo . '%');
    if (!empty($status)) $stmt->bindValue(':status', $status);
    $stmt->bindValue(':limite', $limite, \PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
    $stmt->execute();

    return [
        'dados' => $stmt->fetchAll(),
        'total' => $total,
        'pagina_atual' => $pagina,
        'total_paginas' => $totalPaginas
    ];
}
}