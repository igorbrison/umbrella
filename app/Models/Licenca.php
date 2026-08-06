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
 * 
 * Conexão: Utiliza o Singleton Database para obter uma instância PDO única.
 */

require_once __DIR__ . '/Database.php';

class Licenca {
    
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
     * Busca a licença de um cliente específico.
     * 
     * @param int $clienteId ID do cliente.
     * @return array|null Dados da licença ou null se não encontrada.
     */
    public function buscarPorCliente(int $clienteId): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM licencas WHERE cliente_id = :cid");
        $stmt->execute([':cid' => $clienteId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Cria ou atualiza a licença de um cliente.
     * A data de expiração é calculada automaticamente para o próximo dia 5.
     * Se já existir uma licença para o cliente, ela é atualizada;
     * caso contrário, uma nova é inserida.
     * 
     * @param int $clienteId ID do cliente.
     * @param string $chave Nova chave de licença.
     * @return bool True se a operação for bem-sucedida.
     */
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

    /**
     * Desativa uma licença (soft delete).
     * Apenas marca o campo 'ativa' como 0, sem remover o registro.
     * 
     * @param int $clienteId ID do cliente.
     * @return bool True se a operação for bem-sucedida.
     */
    public function desativar(int $clienteId): bool {
        $stmt = $this->pdo->prepare("UPDATE licencas SET ativa = 0 WHERE cliente_id = :cid");
        return $stmt->execute([':cid' => $clienteId]);
    }

    /**
     * Gera uma nova chave de licença aleatória (64 caracteres hexadecimais).
     * 
     * @return string Chave gerada.
     */
    public function gerarChave(): string {
        return bin2hex(random_bytes(32));
    }

    /**
     * Lista licenças de todos os clientes vinculados a um representante.
     * Inclui dados auxiliares: nome do cliente, CPF/CNPJ e valor total.
     * 
     * @param int $representanteId ID do representante.
     * @return array Lista de licenças.
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
     * Inclui o nome do cliente, CPF/CNPJ, valor total e nome do representante.
     * 
     * @return array Lista de todas as licenças.
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

    /**
     * Calcula a data do próximo dia 5.
     * Se hoje é antes do dia 5, retorna o dia 5 do mês atual.
     * Se hoje é dia 5 ou posterior, retorna o dia 5 do mês seguinte.
     * 
     * @return string Data no formato 'Y-m-d'.
     */
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