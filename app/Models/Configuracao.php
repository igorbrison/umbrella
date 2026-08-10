<?php
/**
 * Arquivo: Models/Configuracao.php
 * Função: Model da entidade "Configuracao".
 * 
 * Responsável por:
 *   - Gerenciar as configurações globais do sistema (chave/valor).
 *   - Oferecer métodos para obter e definir qualquer configuração.
 *   - Manter compatibilidade com os métodos antigos (getSalarioMinimo, setSalarioMinimo).
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
     * Obtém o valor de uma configuração pelo nome da chave.
     * 
     * @param string $chave Nome da chave de configuração.
     * @return string|null Valor da configuração ou null se não encontrada.
     */
    public function get(string $chave): ?string {
        $stmt = $this->pdo->prepare("SELECT valor FROM configuracoes WHERE chave = :chave");
        $stmt->execute([':chave' => $chave]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['valor'] : null;
    }

    /**
     * Define (insere ou atualiza) o valor de uma configuração.
     * Converte automaticamente para string, garantindo compatibilidade com a coluna VARCHAR.
     * 
     * @param string $chave Nome da chave de configuração.
     * @param mixed  $valor Novo valor a ser armazenado (será convertido para string).
     */
    public function set(string $chave, $valor): void {
        // Garante que o valor seja sempre uma string válida
        $valor = is_null($valor) ? '' : (string) $valor;

        $stmt = $this->pdo->prepare(
            "INSERT INTO configuracoes (chave, valor) VALUES (:chave, :valor) 
             ON DUPLICATE KEY UPDATE valor = :valor2"
        );
        $stmt->execute([
            ':chave'  => $chave,
            ':valor'  => $valor,
            ':valor2' => $valor
        ]);
    }

    /**
     * Obtém o valor do salário mínimo (método legado mantido para compatibilidade).
     * 
     * @return float Salário mínimo atual.
     */
    public function getSalarioMinimo(): float {
        $valor = $this->get('salario_minimo');
        return $valor !== null ? (float)$valor : 1621.00;
    }

    /**
     * Define o valor do salário mínimo (método legado mantido para compatibilidade).
     * 
     * @param float $valor Novo valor do salário mínimo.
     */
    public function setSalarioMinimo(float $valor): void {
        $this->set('salario_minimo', (string) $valor);
    }
}