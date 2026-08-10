<?php
/**
 * Arquivo: Models/Representante.php
 * Função: MODEL da entidade "Representante".
 * 
 * Responsabilidades:
 *   - Gerenciar todas as operações de banco de dados relacionadas aos representantes.
 *   - Aplicar regras de negócio antes de persistir os dados (ex: hash de senha).
 *   - Garantir a segurança contra SQL Injection (uso de prepared statements).
 *   - Oferecer métodos específicos para ordenação, busca, inserção, atualização, exclusão e alteração de status.
 *   - Paginação e cálculo de comissões.
 */

require_once __DIR__ . '/Database.php';

class Representante {
    
    private \PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    /**
     * Retorna a instância PDO (para consultas específicas no controller).
     */
    public function getPdo(): \PDO {
        return $this->pdo;
    }

    /**
     * Lista representantes com paginação e ordenação.
     */
    public function listarPaginado(string $ordem = 'id', string $direcao = 'asc', int $pagina = 1, int $limite = 10): array
    {
        $colunasPermitidas = ['id', 'nome_razao', 'nome_fantasia', 'cnpj', 'email', 'comissao_percentual', 'ativo'];
        if (!in_array($ordem, $colunasPermitidas)) {
            $ordem = 'id';
        }
        $direcao = strtolower($direcao) === 'desc' ? 'DESC' : 'ASC';
        $offset = ($pagina - 1) * $limite;

        $total = (int)$this->pdo->query("SELECT COUNT(*) FROM representantes")->fetchColumn();
        $totalPaginas = $total > 0 ? (int)ceil($total / $limite) : 1;

        $sql = "SELECT * FROM representantes ORDER BY $ordem $direcao LIMIT $limite OFFSET $offset";
        $dados = $this->pdo->query($sql)->fetchAll();

        return [
            'dados'          => $dados,
            'total'          => $total,
            'pagina_atual'   => $pagina,
            'total_paginas'  => $totalPaginas,
        ];
    }

    public function listarComOrdenacao(string $coluna = 'id', string $direcao = 'asc'): array {
        return $this->listarPaginado($coluna, $direcao, 1, 10000)['dados'];
    }

    public function listarTodos(): array {
        return $this->listarComOrdenacao('id', 'asc');
    }

    public function buscarPorId(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM representantes WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function buscarPorEmail(string $email): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM representantes WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function inserir(array $dados): bool {
        if (!empty($dados[':senha'])) {
            $dados[':senha'] = password_hash($dados[':senha'], PASSWORD_DEFAULT);
        }
        $sql = "INSERT INTO representantes 
        (cnpj, inscricao_estadual, nome_razao, nome_fantasia, nome_exibicao, cnae, crt,
         data_fundacao, comissao_percentual, logradouro, numero, complemento,
         bairro, cep, estado, municipio, telefone, celular, email, observacoes, ativo, senha)
        VALUES 
        (:cnpj, :inscricao_estadual, :nome_razao, :nome_fantasia, :nome_exibicao, :cnae, :crt,
         :data_fundacao, :comissao_percentual, :logradouro, :numero, :complemento,
         :bairro, :cep, :estado, :municipio, :telefone, :celular, :email, :observacoes, :ativo, :senha)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($dados);
    }

    public function atualizar(int $id, array $dados): bool {
        if (!empty($dados[':senha'])) {
            $dados[':senha'] = password_hash($dados[':senha'], PASSWORD_DEFAULT);
            $sql = "UPDATE representantes SET 
                    cnpj=:cnpj, inscricao_estadual=:inscricao_estadual, nome_razao=:nome_razao, nome_fantasia=:nome_fantasia,
                    nome_exibicao=:nome_exibicao,
                    cnae=:cnae, crt=:crt, data_fundacao=:data_fundacao,
                    comissao_percentual=:comissao_percentual, logradouro=:logradouro, numero=:numero,
                    complemento=:complemento, bairro=:bairro, cep=:cep, estado=:estado,
                    municipio=:municipio, telefone=:telefone, celular=:celular, email=:email,
                    observacoes=:observacoes, ativo=:ativo, senha=:senha
                    WHERE id=:id";
        } else {
            unset($dados[':senha']);
            $sql = "UPDATE representantes SET 
                    cnpj=:cnpj, inscricao_estadual=:inscricao_estadual, nome_razao=:nome_razao, nome_fantasia=:nome_fantasia,
                    nome_exibicao=:nome_exibicao,
                    cnae=:cnae, crt=:crt, data_fundacao=:data_fundacao,
                    comissao_percentual=:comissao_percentual, logradouro=:logradouro, numero=:numero,
                    complemento=:complemento, bairro=:bairro, cep=:cep, estado=:estado,
                    municipio=:municipio, telefone=:telefone, celular=:celular, email=:email,
                    observacoes=:observacoes, ativo=:ativo
                    WHERE id=:id";
        }
        $dados[':id'] = $id;
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($dados);
    }

    public function alterarStatus(int $id, int $ativo): bool {
        $stmt = $this->pdo->prepare("UPDATE representantes SET ativo = :ativo WHERE id = :id");
        return $stmt->execute([':ativo' => $ativo, ':id' => $id]);
    }

    public function excluir(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM representantes WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function buscarPorCnpj(string $cnpj): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM representantes WHERE cnpj = :cnpj");
        $stmt->execute([':cnpj' => $cnpj]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Calcula o total de comissão devida a um representante no período
     * do dia 10 do mês anterior até o dia 10 do mês de referência.
     */
    public function getValorComissao(int $representanteId, string $mesReferencia): float
    {
        $dataRef = DateTime::createFromFormat('Y-m', $mesReferencia);
        $dataFim = $dataRef->format('Y-m-10');
        $dataInicio = (clone $dataRef)->modify('-1 month')->format('Y-m-10');

        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(p.valor), 0)
            FROM pagamentos p
            JOIN clientes c ON p.cliente_id = c.id
            WHERE c.representante_id = :rid
              AND p.data_pagamento BETWEEN :inicio AND :fim
        ");
        $stmt->execute([
            ':rid'    => $representanteId,
            ':inicio' => $dataInicio,
            ':fim'    => $dataFim
        ]);
        $totalPago = (float)$stmt->fetchColumn();

        $rep = $this->buscarPorId($representanteId);
        $percentual = $rep ? (float)$rep['comissao_percentual'] : 0;

        return round($totalPago * $percentual / 100, 2);
    }

    /**
     * Retorna os clientes de um representante com os valores de comissão
     * para o período do dia 10 do mês anterior ao dia 10 do mês atual.
     */
    public function buscarClientesComissao(int $representanteId, string $mesReferencia): array
    {
        $rep = $this->buscarPorId($representanteId);
        $percentual = $rep ? (float)$rep['comissao_percentual'] : 0;

        $dataRef = DateTime::createFromFormat('Y-m', $mesReferencia);
        $dataFim = $dataRef->format('Y-m-10');
        $dataInicio = (clone $dataRef)->modify('-1 month')->format('Y-m-10');

        $stmt = $this->pdo->prepare("
            SELECT c.nome AS cliente_nome, c.cpf_cnpj,
                   COALESCE(p.valor, 0) AS total_pago,
                   ROUND(COALESCE(p.valor, 0) * :percentual / 100, 2) AS comissao
            FROM clientes c
            LEFT JOIN (
                SELECT cliente_id, SUM(valor) AS valor
                FROM pagamentos
                WHERE data_pagamento BETWEEN :inicio AND :fim
                GROUP BY cliente_id
            ) p ON p.cliente_id = c.id
            WHERE c.representante_id = :rid
            ORDER BY c.nome
        ");
        $stmt->execute([
            ':percentual' => $percentual,
            ':inicio'     => $dataInicio,
            ':fim'        => $dataFim,
            ':rid'        => $representanteId,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna uma lista de todos os representantes com o valor da comissão devida em um mês específico.
     */
    public function comissaoPorRepresentante(?string $mesReferencia = null): array
    {
        if ($mesReferencia === null) {
            $data = new DateTime('first day of last month');
            $mesReferencia = $data->format('Y-m');
        }

        $representantes = $this->listarTodos();
        foreach ($representantes as &$r) {
            $r['comissao_devida'] = $this->getValorComissao($r['id'], $mesReferencia);
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(valor), 0) FROM comissao_pagamentos
                WHERE representante_id = :rid AND mes_referencia = :mes
            ");
            $stmt->execute([':rid' => $r['id'], ':mes' => $mesReferencia]);
            $r['comissao_paga'] = (float)$stmt->fetchColumn();
        }
        return $representantes;
    }

    /**
     * Registra o pagamento de comissão a um representante.
     */
    public function registrarPagamentoComissao(int $representanteId, float $valor, string $mesReferencia, string $observacao = ''): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO comissao_pagamentos (representante_id, valor, data_pagamento, mes_referencia, observacao)
            VALUES (:rid, :valor, CURDATE(), :mes, :obs)
        ");
        $stmt->execute([
            ':rid' => $representanteId,
            ':valor' => $valor,
            ':mes' => $mesReferencia,
            ':obs' => $observacao
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Atualiza a observação de um registro de comissao_pagamentos.
     */
    public function atualizarObservacaoComissao(int $id, string $observacao): void
    {
        $stmt = $this->pdo->prepare("UPDATE comissao_pagamentos SET observacao = :obs WHERE id = :id");
        $stmt->execute([':obs' => $observacao, ':id' => $id]);
    }
}