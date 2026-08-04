<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Configuracao.php';

class Modulo {
    private \PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    /**
     * Calcula o valor real do módulo com base no percentual e no salário mínimo.
     */
    public function getValorCalculado(float $salarioMinimo, ?float $percentual): float {
        return $percentual !== null ? round($salarioMinimo * $percentual / 100, 2) : 0.0;
    }

    /**
     * Lista todos os módulos, já com o valor calculado, ordenados por nome.
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
     * Lista com ordenação dinâmica, já incluindo o valor calculado.
     * A coluna 'valor' não existe mais, mas podemos ordenar por 'percentual_salario_minimo'.
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

    public function buscarPorId(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM modulos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

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

    public function excluir(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM modulos WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Soma os valores calculados de uma lista de IDs, usando o percentual e salário mínimo atual.
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