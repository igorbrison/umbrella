<?php
/**
 * Arquivo: Models/Configuracao.php
 * Função: Model para acesso às configurações globais do sistema.
 * 
 * Responsável por:
 *   - Obter e atualizar o valor do salário mínimo (usado como base para
 *     o cálculo dos preços dos módulos).
 * 
 * Conexão: Utiliza o Singleton Database para obter uma instância PDO única.
 */

require_once __DIR__ . '/Database.php';

class Configuracao {
    
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
     * Retorna o valor atual do salário mínimo cadastrado.
     * 
     * @return float Valor do salário mínimo (padrão 1621.00 caso não exista).
     */
    public function getSalarioMinimo(): float {
        $stmt = $this->pdo->query("SELECT valor FROM configuracoes WHERE chave = 'salario_minimo'");
        $row = $stmt->fetch();
        return $row ? (float)$row['valor'] : 1621.00;
    }

    /**
     * Atualiza o valor do salário mínimo no banco de dados.
     * 
     * @param float $valor Novo valor do salário mínimo.
     */
    public function setSalarioMinimo(float $valor): void {
        $stmt = $this->pdo->prepare("UPDATE configuracoes SET valor = :val WHERE chave = 'salario_minimo'");
        $stmt->execute([':val' => $valor]);
    }
}