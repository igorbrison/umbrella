<?php
/**
 * Arquivo: Models/Licenca.php
 * Função: Model da entidade "Licenca".
 * 
 * Responsável por:
 *   - Gerenciar as licenças dos clientes (criação, renovação, desativação).
 *   - Calcular automaticamente a data de expiração (sempre no próximo dia 5).
 *   - Listar licenças por representante ou para o administrador.
 *   - Gerar chaves de licença aleatórias.
 *   - Listagem paginada e com busca para a tela unificada de clientes (admin).
 *   - Indicadores para o Dashboard (contagens e detalhes).
 */

require_once __DIR__ . '/Database.php';

class Licenca {

    private \PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    // ==================== MÉTODOS ORIGINAIS ====================

    public function buscarPorCliente(int $clienteId): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM licencas WHERE cliente_id = :cid");
        $stmt->execute([':cid' => $clienteId]);
        return $stmt->fetch() ?: null;
    }

    public function criarOuAtualizar(int $clienteId, string $chave, int $qtdMaquinas = 1): bool {
        $dataExpiracao = $this->calcularExpiracaoDia5();
        $existente = $this->buscarPorCliente($clienteId);
        if ($existente) {
            $stmt = $this->pdo->prepare(
                "UPDATE licencas SET chave = :chave, data_expiracao = :exp, qtd_maquinas = :qtd, ativa = 1 WHERE cliente_id = :cid"
            );
        } else {
            $stmt = $this->pdo->prepare(
                "INSERT INTO licencas (cliente_id, chave, data_expiracao, qtd_maquinas, ativa)
                 VALUES (:cid, :chave, :exp, :qtd, 1)"
            );
        }
        return $stmt->execute([
            ':cid'   => $clienteId,
            ':chave' => $chave,
            ':exp'   => $dataExpiracao,
            ':qtd'   => $qtdMaquinas
        ]);
    }

    public function desativar(int $clienteId): bool {
        $stmt = $this->pdo->prepare("UPDATE licencas SET ativa = 0 WHERE cliente_id = :cid");
        return $stmt->execute([':cid' => $clienteId]);
    }

    public function gerarChave(): string {
        return bin2hex(random_bytes(32));
    }

    public function listarPorRepresentante(int $representanteId): array {
        $stmt = $this->pdo->prepare(
            "SELECT l.*, c.nome as cliente_nome, c.cpf_cnpj, c.valor_total
             FROM licencas l 
             JOIN clientes c ON l.cliente_id = c.id 
             WHERE c.representante_id = :rid 
             ORDER BY c.nome ASC"
        );
        $stmt->execute([':rid' => $representanteId]);
        return $stmt->fetchAll();
    }

    public function listarTodas(): array {
        $stmt = $this->pdo->query(
            "SELECT l.*, c.nome as cliente_nome, c.cpf_cnpj, c.valor_total, r.nome_razao as representante_nome
             FROM licencas l
             JOIN clientes c ON l.cliente_id = c.id
             JOIN representantes r ON c.representante_id = r.id
             ORDER BY c.nome ASC"
        );
        return $stmt->fetchAll();
    }

    private function calcularExpiracaoDia5(): string {
        $hoje = new DateTime();
        $ano = (int)$hoje->format('Y');
        $mes = (int)$hoje->format('m');
        $dia = (int)$hoje->format('d');

        if ($dia < 5) {
            $dataExpiracao = new DateTime("$ano-$mes-05");
        } else {
            $mes++;
            if ($mes > 12) {
                $mes = 1;
                $ano++;
            }
            $dataExpiracao = new DateTime("$ano-$mes-05");
        }
        return $dataExpiracao->format('Y-m-d');
    }

    public function contarPorStatus(int $representanteId): array {
        $stmt = $this->pdo->prepare(
            "SELECT 
                SUM(CASE WHEN l.ativa = 1 AND l.data_expiracao >= CURDATE() THEN 1 ELSE 0 END) as ativas,
                SUM(CASE WHEN l.ativa = 0 OR l.data_expiracao < CURDATE() THEN 1 ELSE 0 END) as expiradas
             FROM licencas l
             JOIN clientes c ON l.cliente_id = c.id
             WHERE c.representante_id = :rid"
        );
        $stmt->execute([':rid' => $representanteId]);
        return $stmt->fetch() ?: ['ativas' => 0, 'expiradas' => 0];
    }

    // ==================== TELA UNIFICADA DE CLIENTES (ADMIN) ====================

    public function listarTodasPaginado(int $pagina, int $limite, string $termo, string $ordem, string $direcao): array
    {
        $offset = ($pagina - 1) * $limite;
        $where = '';
        $params = [];
        if ($termo) {
            $where = " AND (c.nome LIKE :termo OR c.cpf_cnpj LIKE :termo OR c.email LIKE :termo)";
            $params[':termo'] = "%$termo%";
        }

        $colunasPermitidas = [
            'cliente_nome'        => 'c.nome',
            'cpf_cnpj'            => 'c.cpf_cnpj',
            'representante_nome'  => 'r.nome_razao',
            'data_expiracao'      => 'l.data_expiracao',
            'valor_total_atual'   => 'c.valor_total',   // ← corrigido
            'ativa'               => 'l.ativa',
        ];
        $coluna = $colunasPermitidas[$ordem] ?? 'c.nome';
        $direcao = strtoupper($direcao) === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT l.*, c.nome AS cliente_nome, c.cpf_cnpj, c.email, r.nome_razao AS representante_nome,
                       c.valor_total
                FROM licencas l
                JOIN clientes c ON c.id = l.cliente_id
                JOIN representantes r ON r.id = c.representante_id
                WHERE 1=1 $where
                ORDER BY $coluna $direcao
                LIMIT $limite OFFSET $offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarTodas(string $termo): int
    {
        $where = '';
        $params = [];
        if ($termo) {
            $where = " AND (c.nome LIKE :termo OR c.cpf_cnpj LIKE :termo OR c.email LIKE :termo)";
            $params[':termo'] = "%$termo%";
        }
        $sql = "SELECT COUNT(*) FROM licencas l
                JOIN clientes c ON c.id = l.cliente_id
                JOIN representantes r ON r.id = c.representante_id
                WHERE 1=1 $where";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    // ==================== DASHBOARD ====================

    public function contarAtivas(): int {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM licencas WHERE ativa = 1 AND data_expiracao >= CURDATE()");
        return (int)$stmt->fetchColumn();
    }

    public function contarExpiradas(): int {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM licencas WHERE ativa = 0 OR data_expiracao < CURDATE()");
        return (int)$stmt->fetchColumn();
    }

    public function contarAtivasPorRepresentante(int $repId): int {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM licencas l
            JOIN clientes c ON l.cliente_id = c.id
            WHERE c.representante_id = :rid AND l.ativa = 1 AND l.data_expiracao >= CURDATE()
        ");
        $stmt->execute([':rid' => $repId]);
        return (int)$stmt->fetchColumn();
    }

    public function contarExpiradasPorRepresentante(int $repId): int {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM licencas l
            JOIN clientes c ON l.cliente_id = c.id
            WHERE c.representante_id = :rid AND (l.ativa = 0 OR l.data_expiracao < CURDATE())
        ");
        $stmt->execute([':rid' => $repId]);
        return (int)$stmt->fetchColumn();
    }

    public function listarParaDetalhes(): array {
        $stmt = $this->pdo->query("
            SELECT l.cliente_id, c.nome AS cliente_nome
            FROM licencas l
            JOIN clientes c ON l.cliente_id = c.id
            ORDER BY c.nome
        ");
        return $stmt->fetchAll();
    }

    public function listarPorRepresentanteDetalhes(int $repId): array {
        $stmt = $this->pdo->prepare("
            SELECT l.cliente_id, c.nome AS cliente_nome
            FROM licencas l
            JOIN clientes c ON l.cliente_id = c.id
            WHERE c.representante_id = :rid
            ORDER BY c.nome
        ");
        $stmt->execute([':rid' => $repId]);
        return $stmt->fetchAll();
    }
}