<?php
/**
 * Arquivo: Models/Cliente.php
 * Função: Model da entidade "Cliente".
 * 
 * Responsável por:
 *   - Gerenciar todas as operações de banco de dados relacionadas aos clientes.
 *   - Oferecer métodos de listagem, busca, inserção, atualização e exclusão.
 *   - Calcular o valor total atual do cliente com base nos módulos contratados
 *     e no salário mínimo vigente.
 *   - Permitir operações administrativas (sem restrição de representante).
 * 
 * Conexão: Utiliza o Singleton Database para obter uma instância PDO única.
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Configuracao.php';

class Cliente {
    
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
     * Lista todos os clientes vinculados a um representante, com ordenação.
     * 
     * @param int $representanteId ID do representante.
     * @param string $ordem Coluna para ordenação (padrão: 'id').
     * @param string $direcao Direção da ordenação: 'asc' ou 'desc' (padrão: 'asc').
     * @return array Lista de clientes.
     * 
     * SEGURANÇA: Utiliza lista branca de colunas para evitar SQL Injection.
     */
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

    /**
     * Busca um cliente pelo ID, validando o representante vinculado.
     * 
     * @param int $id ID do cliente.
     * @param int $representanteId ID do representante dono do cliente.
     * @return array|null Dados do cliente ou null se não encontrado.
     */
    public function buscarPorId(int $id, int $representanteId): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM clientes WHERE id = :id AND representante_id = :rid");
        $stmt->execute([':id' => $id, ':rid' => $representanteId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Busca cliente por ID sem restrição de representante (uso administrativo).
     * 
     * @param int $id ID do cliente.
     * @return array|null Dados do cliente ou null se não encontrado.
     */
    public function buscarPorIdAdmin(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM clientes WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Insere um novo cliente no banco de dados.
     * 
     * @param array $dados Array associativo com os dados do cliente.
     * @return bool True se a inserção for bem-sucedida.
     */
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

    /**
     * Atualiza os dados de um cliente (uso pelo representante).
     * 
     * @param int $id ID do cliente.
     * @param int $representanteId ID do representante dono do cliente.
     * @param array $dados Array associativo com os dados a atualizar.
     * @return bool True se a atualização for bem-sucedida.
     */
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
     * 
     * @param int $id ID do cliente.
     * @param array $dados Array associativo com os dados a atualizar.
     * @return bool True se a atualização for bem-sucedida.
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

    /**
     * Atualiza apenas o campo valor_total de um cliente.
     * 
     * @param int $id ID do cliente.
     * @param float $valor Novo valor total.
     */
    public function atualizarValorTotal(int $id, float $valor): void {
        $stmt = $this->pdo->prepare("UPDATE clientes SET valor_total = :val WHERE id = :id");
        $stmt->execute([':val' => $valor, ':id' => $id]);
    }

    /**
     * Calcula o valor total atual do cliente, baseado nos módulos contratados
     * e no salário mínimo em vigor.
     * 
     * @param int $clienteId ID do cliente.
     * @return float Valor total calculado.
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

    /**
     * Exclui um cliente (hard delete).
     * 
     * @param int $id ID do cliente.
     * @param int $representanteId ID do representante dono do cliente.
     * @return bool True se a exclusão for bem-sucedida.
     * 
     * ATENÇÃO: Esta operação é irreversível. Em sistemas críticos, considere
     * usar 'soft delete' (apenas marcar como inativo) em vez de exclusão física.
     */
    public function excluir(int $id, int $representanteId): bool {
        $stmt = $this->pdo->prepare("DELETE FROM clientes WHERE id = :id AND representante_id = :rid");
        return $stmt->execute([':id' => $id, ':rid' => $representanteId]);
    }

    public function listarPaginadoPorRepresentante(int $representanteId, string $ordem = 'id', string $direcao = 'asc', int $pagina = 1, int $limite = 10): array {
    $colunasPermitidas = ['id', 'nome', 'cpf_cnpj', 'email', 'ativo'];
    if (!in_array($ordem, $colunasPermitidas)) $ordem = 'id';
    $direcao = strtolower($direcao) === 'desc' ? 'DESC' : 'ASC';

    $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM clientes WHERE representante_id = :rid");
    $stmt->execute([':rid' => $representanteId]);
    $total = (int)$stmt->fetchColumn();

    $totalPaginas = $total > 0 ? (int)ceil($total / $limite) : 1;
    $offset = ($pagina - 1) * $limite;

    $sql = "SELECT * FROM clientes WHERE representante_id = :rid ORDER BY $ordem $direcao LIMIT :limite OFFSET :offset";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':rid', $representanteId, \PDO::PARAM_INT);
    $stmt->bindValue(':limite', $limite, \PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
    $stmt->execute();

    return [
        'dados' => $stmt->fetchAll(),
        'total' => $total,
        'pagina_atual' => $pagina,
        'total_paginas' => $totalPaginas
    ];
}

public function buscarPaginadoPorRepresentante(int $representanteId, string $termo, int $pagina = 1, int $limite = 10, string $ordem = 'id', string $direcao = 'asc'): array {
    $colunasPermitidas = ['id', 'nome', 'cpf_cnpj', 'email', 'ativo'];
    if (!in_array($ordem, $colunasPermitidas)) $ordem = 'id';
    $direcao = strtolower($direcao) === 'desc' ? 'DESC' : 'ASC';

    $where = "representante_id = :rid AND (nome LIKE :termo OR cpf_cnpj LIKE :termo OR email LIKE :termo)";
    $params = [':rid' => $representanteId, ':termo' => '%' . $termo . '%'];

    $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM clientes WHERE $where");
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();

    $totalPaginas = $total > 0 ? (int)ceil($total / $limite) : 1;
    $offset = ($pagina - 1) * $limite;

    $sql = "SELECT * FROM clientes WHERE $where ORDER BY $ordem $direcao LIMIT :limite OFFSET :offset";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':rid', $representanteId, \PDO::PARAM_INT);
    $stmt->bindValue(':termo', '%' . $termo . '%');
    $stmt->bindValue(':limite', $limite, \PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
    $stmt->execute();

    return [
        'dados' => $stmt->fetchAll(),
        'total' => $total,
        'pagina_atual' => $pagina,
        'total_paginas' => $totalPaginas
    ];
}
}