<?php
/**
 * Arquivo: Models/Modulo.php
 * Função: Model da entidade "Módulo".
 * 
 * Responsável por:
 *   - Gerenciar todas as operações de banco de dados relacionadas aos módulos.
 *   - Calcular o valor em reais de cada módulo com base no percentual do salário mínimo vigente.
 *   - Oferecer métodos de listagem, busca, inserção, atualização, exclusão, soma de valores e listagem paginada.
 * 
 * Conexão: Utiliza o Singleton Database para obter uma instância PDO única.
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Configuracao.php';

class Modulo {
    
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
     * Calcula o valor real do módulo com base no percentual e no salário mínimo.
     * 
     * @param float $salarioMinimo Valor do salário mínimo vigente.
     * @param float|null $percentual Percentual do salário mínimo (pode ser nulo).
     * @return float Valor calculado do módulo em reais.
     */
    public function getValorCalculado(float $salarioMinimo, ?float $percentual): float {
        return $percentual !== null ? round($salarioMinimo * $percentual / 100, 2) : 0.0;
    }

    /**
     * Lista todos os módulos, já com o valor calculado, ordenados por nome.
     * 
     * @return array Lista de módulos (cada elemento contém o campo 'valor' calculado).
     */
    public function listarTodos(): array {
        $salarioMinimo = (new Configuracao())->getSalarioMinimo();
        $stmt = $this->pdo->query("SELECT * FROM modulos ORDER BY nome ASC");
        $modulos = $stmt->fetchAll();
        foreach ($modulos as &$m) {
            $m['valor'] = $this->getValorCalculado($salarioMinimo, $m['percentual_salario_minimo']);
        }
        return $modulos;
    }

    /**
     * Lista módulos com ordenação dinâmica, já incluindo o valor calculado.
     * A ordenação pode ser feita por 'id', 'identificador', 'nome', 'percentual_salario_minimo' ou 'ativo'.
     * 
     * @param string $coluna Coluna para ordenação (padrão: 'id').
     * @param string $direcao Direção da ordenação: 'asc' ou 'desc' (padrão: 'asc').
     * @return array Lista de módulos ordenados.
     * 
     * SEGURANÇA: Utiliza lista branca de colunas para evitar SQL Injection.
     */
    public function listarComOrdenacao(string $coluna = 'id', string $direcao = 'asc'): array {
        $colunasPermitidas = ['id', 'identificador', 'nome', 'percentual_salario_minimo', 'ativo'];
        if (!in_array($coluna, $colunasPermitidas)) {
            $coluna = 'id';
        }
        $direcao = strtolower($direcao) === 'desc' ? 'DESC' : 'ASC';
        $sql = "SELECT * FROM modulos ORDER BY $coluna $direcao";
        $stmt = $this->pdo->query($sql);
        $modulos = $stmt->fetchAll();

        $salarioMinimo = (new Configuracao())->getSalarioMinimo();
        foreach ($modulos as &$m) {
            $m['valor'] = $this->getValorCalculado($salarioMinimo, $m['percentual_salario_minimo']);
        }
        return $modulos;
    }

    /**
     * Lista módulos com paginação, ordenação e valor calculado.
     * 
     * @param int    $pagina  Número da página.
     * @param int    $limite  Registros por página.
     * @param string $ordem   Coluna de ordenação.
     * @param string $direcao Direção da ordenação.
     * @return array Lista de módulos da página atual.
     */
    public function listarPaginado(int $pagina, int $limite, string $ordem = 'id', string $direcao = 'asc'): array {
        $offset = ($pagina - 1) * $limite;
        $colunasPermitidas = ['id', 'identificador', 'nome', 'percentual_salario_minimo', 'ativo'];
        if (!in_array($ordem, $colunasPermitidas)) {
            $ordem = 'id';
        }
        $direcao = strtolower($direcao) === 'desc' ? 'DESC' : 'ASC';
        $sql = "SELECT * FROM modulos ORDER BY $ordem $direcao LIMIT $limite OFFSET $offset";
        $stmt = $this->pdo->query($sql);
        $modulos = $stmt->fetchAll();

        $salarioMinimo = (new Configuracao())->getSalarioMinimo();
        foreach ($modulos as &$m) {
            $m['valor'] = $this->getValorCalculado($salarioMinimo, $m['percentual_salario_minimo']);
        }
        return $modulos;
    }

    /**
     * Conta o total de módulos cadastrados (para paginação).
     * 
     * @return int Número total de módulos.
     */
    public function contarTodos(): int {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM modulos")->fetchColumn();
    }

    /**
     * Busca um módulo pelo ID.
     * 
     * @param int $id ID do módulo.
     * @return array|null Dados do módulo ou null se não encontrado.
     */
    public function buscarPorId(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM modulos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Insere um novo módulo no banco de dados.
     * 
     * @param array $dados Array associativo com os dados do módulo.
     * @return bool True se a inserção for bem-sucedida.
     */
    public function inserir(array $dados): bool {
        $sql = "INSERT INTO modulos (identificador, nome, percentual_salario_minimo, descricao, ativo) 
                VALUES (:identificador, :nome, :percentual, :descricao, :ativo)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':identificador' => $dados[':identificador'],
            ':nome' => $dados[':nome'],
            ':percentual' => $dados[':percentual_salario_minimo'] ?? null,
            ':descricao' => $dados[':descricao'] ?? null,
            ':ativo' => $dados[':ativo'] ?? 1
        ]);
    }

    /**
     * Atualiza os dados de um módulo existente.
     * 
     * @param int $id ID do módulo a ser atualizado.
     * @param array $dados Array associativo com os dados a atualizar.
     * @return bool True se a atualização for bem-sucedida.
     */
    public function atualizar(int $id, array $dados): bool {
        $dados[':id'] = $id;
        $sql = "UPDATE modulos SET identificador=:identificador, nome=:nome, 
                percentual_salario_minimo=:percentual, descricao=:descricao, ativo=:ativo 
                WHERE id=:id";
        return $this->pdo->prepare($sql)->execute([
            ':identificador' => $dados[':identificador'],
            ':nome' => $dados[':nome'],
            ':percentual' => $dados[':percentual_salario_minimo'] ?? null,
            ':descricao' => $dados[':descricao'] ?? null,
            ':ativo' => $dados[':ativo'] ?? 1,
            ':id' => $id
        ]);
    }

    /**
     * Exclui um módulo permanentemente.
     * 
     * @param int $id ID do módulo a ser excluído.
     * @return bool True se a exclusão for bem-sucedida.
     */
    public function excluir(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM modulos WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Soma os valores calculados de uma lista de IDs de módulos.
     * Utiliza o percentual de cada módulo e o salário mínimo atual.
     * 
     * @param array $ids Lista de IDs dos módulos.
     * @return float Soma dos valores calculados.
     */
    public function somaValores(array $ids): float {
        if (empty($ids)) return 0.0;
        $salarioMinimo = (new Configuracao())->getSalarioMinimo();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare("SELECT percentual_salario_minimo FROM modulos WHERE id IN ($placeholders)");
        $stmt->execute(array_values($ids));
        $soma = 0.0;
        while ($row = $stmt->fetch()) {
            $soma += $this->getValorCalculado($salarioMinimo, $row['percentual_salario_minimo']);
        }
        return round($soma, 2);
    }
}