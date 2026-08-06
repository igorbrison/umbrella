<?php
/**
 * Arquivo: Models/Admin.php
 * Função: Model da entidade "Administrador".
 * 
 * Responsável por:
 *   - Gerenciar as operações de banco de dados relacionadas aos administradores.
 *   - Oferecer métodos de busca para autenticação e recuperação de senha.
 * 
 * Conexão: Utiliza o Singleton Database para obter uma instância PDO única.
 */

require_once __DIR__ . '/Database.php';

class Admin {
    
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
     * Busca um administrador pelo endereço de e-mail.
     * 
     * @param string $email E-mail do administrador.
     * @return array|null Retorna os dados do administrador ou null se não encontrado.
     * 
     * SEGURANÇA: Utiliza prepared statement para evitar SQL Injection.
     */
    public function buscarPorEmail(string $email): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM administradores WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch() ?: null;
    }
}