<?php
require_once __DIR__ . '/Database.php';

class Licenca {
    private \PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    // Busca licença de um cliente específico
    public function buscarPorCliente(int $clienteId): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM licencas WHERE cliente_id = :cid");
        $stmt->execute([':cid' => $clienteId]);
        return $stmt->fetch() ?: null;
    }

    // Cria ou atualiza a licença, calculando automaticamente a expiração para o próximo dia 5
    public function criarOuAtualizar(int $clienteId, string $chave): bool {
        $dataExpiracao = $this->calcularExpiracaoDia5();
        $existente = $this->buscarPorCliente($clienteId);
        if ($existente) {
            $stmt = $this->pdo->prepare(
                "UPDATE licencas SET chave = :chave, data_expiracao = :exp, ativa = 1 WHERE cliente_id = :cid"
            );
        } else {
            $stmt = $this->pdo->prepare(
                "INSERT INTO licencas (cliente_id, chave, data_expiracao, ativa) VALUES (:cid, :chave, :exp, 1)"
            );
        }
        return $stmt->execute([':cid' => $clienteId, ':chave' => $chave, ':exp' => $dataExpiracao]);
    }

    // Desativa uma licença (soft delete)
    public function desativar(int $clienteId): bool {
        $stmt = $this->pdo->prepare("UPDATE licencas SET ativa = 0 WHERE cliente_id = :cid");
        return $stmt->execute([':cid' => $clienteId]);
    }

    // Gera uma nova chave de licença aleatória
    public function gerarChave(): string {
        return bin2hex(random_bytes(32));
    }

    /**
     * Lista licenças de todos os clientes vinculados a um representante.
     * @param int $representanteId
     * @return array
     */
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

    /**
     * Lista todas as licenças do sistema (para o administrador).
     * Inclui o nome do representante.
     * @return array
     */
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

    // Calcula a data do próximo dia 5 (se hoje < 5, dia 5 deste mês; senão, dia 5 do mês seguinte)
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
}