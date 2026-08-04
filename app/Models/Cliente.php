<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Configuracao.php';

class Cliente {
    private \PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function listarPorRepresentante(int $representanteId, string $ordem = 'id', string $direcao = 'asc'): array {
        $colunasPermitidas = ['id', 'nome', 'cpf_cnpj', 'email', 'ativo'];
        if (!in_array($ordem, $colunasPermitidas)) {
            $ordem = 'id';
        }
        $direcao = strtolower($direcao) === 'desc' ? 'DESC' : 'ASC';
        $sql = "SELECT * FROM clientes WHERE representante_id = :rid ORDER BY $ordem $direcao";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':rid' => $representanteId]);
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id, int $representanteId): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM clientes WHERE id = :id AND representante_id = :rid");
        $stmt->execute([':id' => $id, ':rid' => $representanteId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Busca cliente por ID sem restrição de representante (uso administrativo).
     */
    public function buscarPorIdAdmin(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM clientes WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function inserir(array $dados): bool {
        $sql = "INSERT INTO clientes 
                (representante_id, tipo_pessoa, cpf_cnpj, ie_rg, nome, nome_fantasia,
                 data_fundacao, telefone, celular, email, logradouro, numero, complemento,
                 bairro, cep, estado, municipio, observacoes, valor_total, ativo)
                VALUES 
                (:representante_id, :tipo_pessoa, :cpf_cnpj, :ie_rg, :nome, :nome_fantasia,
                 :data_fundacao, :telefone, :celular, :email, :logradouro, :numero, :complemento,
                 :bairro, :cep, :estado, :municipio, :observacoes, :valor_total, :ativo)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($dados);
    }

    public function atualizar(int $id, int $representanteId, array $dados): bool {
        $dados[':id'] = $id;
        $dados[':rid'] = $representanteId;
        $sql = "UPDATE clientes SET 
                tipo_pessoa=:tipo_pessoa, cpf_cnpj=:cpf_cnpj, ie_rg=:ie_rg, nome=:nome, nome_fantasia=:nome_fantasia,
                data_fundacao=:data_fundacao, telefone=:telefone, celular=:celular, email=:email,
                logradouro=:logradouro, numero=:numero, complemento=:complemento,
                bairro=:bairro, cep=:cep, estado=:estado, municipio=:municipio,
                observacoes=:observacoes, ativo=:ativo
                WHERE id = :id AND representante_id = :rid";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($dados);
    }

    /**
     * Atualiza cliente sem restrição de representante (uso administrativo).
     */
    public function atualizarAdmin(int $id, array $dados): bool {
        $dados[':id'] = $id;
        $sql = "UPDATE clientes SET 
                tipo_pessoa=:tipo_pessoa, cpf_cnpj=:cpf_cnpj, ie_rg=:ie_rg, nome=:nome, nome_fantasia=:nome_fantasia,
                data_fundacao=:data_fundacao, telefone=:telefone, celular=:celular, email=:email,
                logradouro=:logradouro, numero=:numero, complemento=:complemento,
                bairro=:bairro, cep=:cep, estado=:estado, municipio=:municipio,
                observacoes=:observacoes, ativo=:ativo
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($dados);
    }

    public function atualizarValorTotal(int $id, float $valor): void {
        $stmt = $this->pdo->prepare("UPDATE clientes SET valor_total = :val WHERE id = :id");
        $stmt->execute([':val' => $valor, ':id' => $id]);
    }

    /**
     * Calcula o valor total atual do cliente, baseado nos módulos contratados
     * e no salário mínimo em vigor.
     */
    public function getValorTotalAtual(int $clienteId): float {
        $sql = "SELECT SUM(m.percentual_salario_minimo) as soma_percentual
                FROM cliente_modulos cm
                JOIN modulos m ON cm.modulo_id = m.id
                WHERE cm.cliente_id = :cid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':cid' => $clienteId]);
        $row = $stmt->fetch();
        $somaPercentual = $row['soma_percentual'] ?? 0;
        if ($somaPercentual == 0) return 0.0;

        $configModel = new Configuracao();
        $salarioMinimo = $configModel->getSalarioMinimo();
        return round($salarioMinimo * $somaPercentual / 100, 2);
    }

    public function excluir(int $id, int $representanteId): bool {
        $stmt = $this->pdo->prepare("DELETE FROM clientes WHERE id = :id AND representante_id = :rid");
        return $stmt->execute([':id' => $id, ':rid' => $representanteId]);
    }
}