<?php
/**
 * Arquivo: Models/ClienteModulo.php
 * Função: Model da tabela associativa "Cliente x Módulos".
 * 
 * Responsável por:
 *   - Gerenciar o relacionamento entre clientes e os módulos que contrataram.
 *   - Consultar quais módulos um cliente possui.
 *   - Sincronizar (substituir) a lista de módulos de um cliente.
 * 
 * Conexão: Utiliza o Singleton Database para obter uma instância PDO única.
 */

require_once __DIR__ . '/Database.php';

class ClienteModulo {
    
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
     * Retorna todos os módulos contratados por um cliente específico.
     * 
     * @param int $clienteId ID do cliente.
     * @return array Lista de módulos (identificador e nome).
     */
    public function getModulosDoCliente(int $clienteId): array {
        $stmt = $this->pdo->prepare(
            "SELECT m.identificador, m.nome FROM cliente_modulos cm 
             JOIN modulos m ON cm.modulo_id = m.id 
             WHERE cm.cliente_id = :cid"
        );
        $stmt->execute([':cid' => $clienteId]);
        return $stmt->fetchAll();
    }

    /**
     * Sincroniza (substitui) a lista de módulos contratados por um cliente.
     * 
     * Remove todos os módulos atuais e insere os novos IDs fornecidos.
     * 
     * @param int $clienteId ID do cliente.
     * @param array $modulosIds Array com os IDs dos módulos a serem associados.
     */
    public function sincronizar(int $clienteId, array $modulosIds): void {
        // Remove todos os módulos atuais do cliente
        $this->pdo->prepare("DELETE FROM cliente_modulos WHERE cliente_id = :cid")->execute([':cid' => $clienteId]);
        // Insere os novos módulos, se houver
        if (!empty($modulosIds)) {
            $stmt = $this->pdo->prepare("INSERT INTO cliente_modulos (cliente_id, modulo_id) VALUES (:cid, :mid)");
            foreach ($modulosIds as $mid) {
                $stmt->execute([':cid' => $clienteId, ':mid' => $mid]);
            }
        }
    }
}