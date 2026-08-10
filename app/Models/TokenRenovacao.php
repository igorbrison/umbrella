<?php
/**
 * Model para gerenciamento de tokens de renovação offline.
 */
require_once __DIR__ . '/Database.php';

class TokenRenovacao
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    /**
     * Gera um token offline para um cliente.
     * O token é um hash aleatório armazenado no banco.
     *
     * @param int $clienteId
     * @return string Token gerado.
     */
    public function gerarTokenOffline(int $clienteId): string
    {
        $token = bin2hex(random_bytes(32));

        $stmt = $this->pdo->prepare(
            "INSERT INTO tokens_renovacao (cliente_id, token) VALUES (:cid, :token)"
        );
        $stmt->execute([':cid' => $clienteId, ':token' => $token]);

        return $token;
    }

    /**
     * Valida se um token é válido para um cliente.
     *
     * @param int    $clienteId
     * @param string $token
     * @return bool
     */
    public function validarToken(int $clienteId, string $token): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT id FROM tokens_renovacao WHERE cliente_id = :cid AND token = :token"
        );
        $stmt->execute([':cid' => $clienteId, ':token' => $token]);
        return (bool) $stmt->fetch();
    }

    /**
     * Remove um token após o uso (renovação realizada).
     *
     * @param int    $clienteId
     * @param string $token
     */
    public function removerToken(int $clienteId, string $token): void
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM tokens_renovacao WHERE cliente_id = :cid AND token = :token"
        );
        $stmt->execute([':cid' => $clienteId, ':token' => $token]);
    }
}