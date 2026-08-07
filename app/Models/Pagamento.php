<?php
require_once __DIR__ . '/Database.php';

class Pagamento {
    private \PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function listarPorCliente(int $clienteId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM pagamentos WHERE cliente_id = :cid ORDER BY data_pagamento DESC");
        $stmt->execute([':cid' => $clienteId]);
        return $stmt->fetchAll();
    }

    public function listarPorRepresentante(int $representanteId, ?string $mes = null): array {
        $sql = "SELECT p.*, c.nome as cliente_nome 
                FROM pagamentos p
                JOIN clientes c ON p.cliente_id = c.id
                WHERE c.representante_id = :rid";
        $params = [':rid' => $representanteId];
        if ($mes) {
            $sql .= " AND p.mes_referencia = :mes";
            $params[':mes'] = $mes;
        }
        $sql .= " ORDER BY p.data_pagamento DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function inserir(int $clienteId, float $valor, string $dataPagamento, string $mesReferencia, ?string $observacao = null): int {
        $stmt = $this->pdo->prepare(
            "INSERT INTO pagamentos (cliente_id, valor, data_pagamento, mes_referencia, observacao)
             VALUES (:cid, :val, :dt, :mes, :obs)"
        );
        $stmt->execute([
            ':cid' => $clienteId,
            ':val' => $valor,
            ':dt' => $dataPagamento,
            ':mes' => $mesReferencia,
            ':obs' => $observacao
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function somaPorMes(int $representanteId, string $mes): float {
        $stmt = $this->pdo->prepare(
            "SELECT SUM(p.valor) FROM pagamentos p
             JOIN clientes c ON p.cliente_id = c.id
             WHERE c.representante_id = :rid AND p.mes_referencia = :mes"
        );
        $stmt->execute([':rid' => $representanteId, ':mes' => $mes]);
        return (float)$stmt->fetchColumn();
    }

    // Listar todos para admin
    public function listarTodos(?string $mes = null): array {
        $sql = "SELECT p.*, c.nome as cliente_nome, r.nome_razao as representante_nome
                FROM pagamentos p
                JOIN clientes c ON p.cliente_id = c.id
                JOIN representantes r ON c.representante_id = r.id";
        $params = [];
        if ($mes) {
            $sql .= " WHERE p.mes_referencia = :mes";
            $params[':mes'] = $mes;
        }
        $sql .= " ORDER BY p.data_pagamento DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}