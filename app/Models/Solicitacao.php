<?php
/**
 * Arquivo: Models/Solicitacao.php
 * Função: Model da entidade "Solicitacao".
 * 
 * Responsável por:
 *   - Gerenciar as solicitações feitas pelos representantes.
 *   - Listar solicitações por representante (painel do representante).
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
}