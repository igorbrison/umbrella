<?php
require_once __DIR__ . '/Database.php';

class Admin {
    private \PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function buscarPorEmail(string $email): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM administradores WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch() ?: null;
    }
}