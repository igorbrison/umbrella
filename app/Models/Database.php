<?php
/**
 * Arquivo: Models/Database.php
 * Função: Singleton de conexão com o banco de dados MySQL.
 * 
 * Responsável por:
 *   - Criar e manter uma única instância da conexão PDO durante toda a requisição.
 *   - Configurar os atributos de erro (Exception) e modo de fetch (array associativo).
 *   - Utilizar as constantes definidas em config.php (DB_HOST, DB_NAME, DB_USER, DB_PASS).
 * 
 * Uso: Chamar Database::getInstance() em qualquer Model para obter a conexão.
 * Exemplo:
 *   $pdo = Database::getInstance();
 *   $stmt = $pdo->query("SELECT * FROM tabela");
 * 
 * Padrão Singleton:
 *   - O construtor é privado, impedindo a criação de múltiplas instâncias.
 *   - O método estático getInstance() retorna sempre a mesma conexão.
 */

class Database {
    
    /**
     * @var Database|null $instance
     * Armazena a única instância da classe (inicialmente nula).
     */
    private static $instance = null;

    /**
     * @var PDO $pdo
     * Objeto de conexão PDO com o banco de dados.
     */
    private $pdo;

    /**
     * Construtor privado (Singleton).
     * Cria a conexão PDO utilizando as constantes de configuração.
     */
    private function __construct() {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,        // Lança exceções em erros
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,   // Retorna arrays associativos
        ]);
    }

    /**
     * Retorna a instância única da conexão PDO.
     * Se ainda não existir, cria a instância.
     * 
     * @return PDO Instância da conexão PDO.
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }
}