<?php
require_once __DIR__ . '/Database.php';

class Configuracao {
    private \PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function getSalarioMinimo(): float {
        $stmt = $this->pdo->query("SELECT valor FROM configuracoes WHERE chave = 'salario_minimo'");
        $row = $stmt->fetch();
        return $row ? (float)$row['valor'] : 1621.00;
    }

    public function setSalarioMinimo(float $valor): void {
        $stmt = $this->pdo->prepare("UPDATE configuracoes SET valor = :val WHERE chave = 'salario_minimo'");
        $stmt->execute([':val' => $valor]);
    }
}