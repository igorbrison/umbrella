<?php
require_once __DIR__ . '/Database.php';

class ClienteModulo {
    private \PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function getModulosDoCliente(int $clienteId): array {
        $stmt = $this->pdo->prepare(
            "SELECT m.identificador, m.nome FROM cliente_modulos cm 
             JOIN modulos m ON cm.modulo_id = m.id 
             WHERE cm.cliente_id = :cid"
        );
        $stmt->execute([':cid' => $clienteId]);
        return $stmt->fetchAll();
    }

    public function sincronizar(int $clienteId, array $modulosIds): void {
        $this->pdo->prepare("DELETE FROM cliente_modulos WHERE cliente_id = :cid")->execute([':cid' => $clienteId]);
        if (!empty($modulosIds)) {
            $stmt = $this->pdo->prepare("INSERT INTO cliente_modulos (cliente_id, modulo_id) VALUES (:cid, :mid)");
            foreach ($modulosIds as $mid) {
                $stmt->execute([':cid' => $clienteId, ':mid' => $mid]);
            }
        }
    }
}