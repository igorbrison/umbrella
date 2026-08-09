<?php
/**
 * Arquivo: Models/Pagamento.php
 * Função: Model da entidade "Pagamento".
 * 
 * Responsável por:
 *   - Gerenciar as operações de banco de dados relacionadas aos pagamentos.
 *   - Registrar pagamentos realizados pelos clientes.
 *   - Listar pagamentos por cliente ou representante.
 *   - Calcular a soma de pagamentos por mês (para gráficos e relatórios).
 *   - Listar todos os pagamentos para o administrador.
 *   - Fornecer indicadores financeiros para o Dashboard (receita mensal e anual).
 *   - Contar pagamentos mensais (equivale a licenças geradas/renovadas).
 * 
 * Conexão: Utiliza o Singleton Database para obter uma instância PDO única.
 */

require_once __DIR__ . '/Database.php';

class Pagamento {
    
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
     * Lista todos os pagamentos de um cliente específico,
     * ordenados do mais recente para o mais antigo.
     * 
     * @param int $clienteId ID do cliente.
     * @return array Lista de pagamentos.
     */
    public function listarPorCliente(int $clienteId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM pagamentos WHERE cliente_id = :cid ORDER BY data_pagamento DESC");
        $stmt->execute([':cid' => $clienteId]);
        return $stmt->fetchAll();
    }

    /**
     * Lista os pagamentos de todos os clientes de um representante.
     * Opcionalmente, filtra por um mês de referência (formato YYYY-MM).
     * 
     * @param int $representanteId ID do representante.
     * @param string|null $mes Mês de referência no formato 'YYYY-MM' (opcional).
     * @return array Lista de pagamentos com nome do cliente.
     */
    public function listarPorRepresentante(int $representanteId, ?string $mes = null): array {
        $sql = "SELECT p.*, c.nome as cliente_nome 
                FROM pagamentos p
                JOIN clientes c ON p.cliente_id = c.id
                WHERE c.representante_id = :rid";
        $params = [':rid' => $representanteId];
        if ($mes) {
            $sql .= " AND p.mes_referencia = :mes";
            $params[':mes'] = $mes;
        }
        $sql .= " ORDER BY p.data_pagamento DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Insere um novo registro de pagamento no banco de dados.
     * 
     * @param int $clienteId ID do cliente que pagou.
     * @param float $valor Valor pago.
     * @param string $dataPagamento Data do pagamento (formato 'Y-m-d').
     * @param string $mesReferencia Mês de referência (formato 'YYYY-MM').
     * @param string|null $observacao Observação opcional sobre o pagamento.
     * @return int ID do pagamento inserido.
     */
    public function inserir(int $clienteId, float $valor, string $dataPagamento, string $mesReferencia, ?string $observacao = null): int {
        $stmt = $this->pdo->prepare(
            "INSERT INTO pagamentos (cliente_id, valor, data_pagamento, mes_referencia, observacao)
             VALUES (:cid, :val, :dt, :mes, :obs)"
        );
        $stmt->execute([
            ':cid' => $clienteId,
            ':val' => $valor,
            ':dt' => $dataPagamento,
            ':mes' => $mesReferencia,
            ':obs' => $observacao
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Calcula a soma total dos pagamentos de um representante em um determinado mês.
     * Utilizado para os gráficos de receita do dashboard.
     * 
     * @param int $representanteId ID do representante.
     * @param string $mes Mês de referência no formato 'YYYY-MM'.
     * @return float Soma dos valores pagos no mês.
     */
    public function somaPorMes(int $representanteId, string $mes): float {
        $stmt = $this->pdo->prepare(
            "SELECT SUM(p.valor) FROM pagamentos p
             JOIN clientes c ON p.cliente_id = c.id
             WHERE c.representante_id = :rid AND p.mes_referencia = :mes"
        );
        $stmt->execute([':rid' => $representanteId, ':mes' => $mes]);
        return (float)$stmt->fetchColumn();
    }

    /**
     * Lista todos os pagamentos do sistema (para o administrador).
     * Inclui o nome do cliente e do representante responsável.
     * Opcionalmente, filtra por um mês de referência.
     * 
     * @param string|null $mes Mês de referência no formato 'YYYY-MM' (opcional).
     * @return array Lista de todos os pagamentos.
     */
    public function listarTodos(?string $mes = null): array {
        $sql = "SELECT p.*, c.nome as cliente_nome, r.nome_razao as representante_nome
                FROM pagamentos p
                JOIN clientes c ON p.cliente_id = c.id
                JOIN representantes r ON c.representante_id = r.id";
        $params = [];
        if ($mes) {
            $sql .= " WHERE p.mes_referencia = :mes";
            $params[':mes'] = $mes;
        }
        $sql .= " ORDER BY p.data_pagamento DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ==================== INDICADORES FINANCEIROS (DASHBOARD) ====================

    /**
     * Obtém a receita mensal global do ano corrente (12 valores, de Jan a Dez).
     * 
     * @return array Lista com 12 floats, um para cada mês.
     */
    public function receitaMensalGlobal(): array {
        $ano = date('Y');
        $stmt = $this->pdo->prepare("
            SELECT MONTH(data_pagamento) AS mes, SUM(valor) AS total
            FROM pagamentos
            WHERE YEAR(data_pagamento) = :ano
            GROUP BY mes
        ");
        $stmt->execute([':ano' => $ano]);
        $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        $valores = array_fill(0, 12, 0.0);
        foreach ($rows as $mes => $total) {
            $valores[(int)$mes - 1] = (float)$total;
        }
        return $valores;
    }

    /**
     * Obtém a receita mensal de um representante no ano corrente.
     * 
     * @param int $representanteId ID do representante.
     * @return array Lista com 12 floats.
     */
    public function receitaMensalPorRepresentante(int $representanteId): array {
        $ano = date('Y');
        $stmt = $this->pdo->prepare("
            SELECT MONTH(p.data_pagamento) AS mes, SUM(p.valor) AS total
            FROM pagamentos p
            JOIN clientes c ON p.cliente_id = c.id
            WHERE c.representante_id = :rid AND YEAR(p.data_pagamento) = :ano
            GROUP BY mes
        ");
        $stmt->execute([':rid' => $representanteId, ':ano' => $ano]);
        $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        $valores = array_fill(0, 12, 0.0);
        foreach ($rows as $mes => $total) {
            $valores[(int)$mes - 1] = (float)$total;
        }
        return $valores;
    }

    /**
     * Calcula a comissão mensal global (10% da receita mensal).
     * 
     * @return array Lista com 12 floats.
     */
    public function comissaoMensalGlobal(): array {
        $receitas = $this->receitaMensalGlobal();
        return array_map(function($v) { return round($v * 0.1, 2); }, $receitas);
    }

    /**
     * Calcula a comissão mensal de um representante (10% da receita mensal).
     * 
     * @param int $representanteId ID do representante.
     * @return array Lista com 12 floats.
     */
    public function comissaoMensalPorRepresentante(int $representanteId): array {
        $receitas = $this->receitaMensalPorRepresentante($representanteId);
        return array_map(function($v) { return round($v * 0.1, 2); }, $receitas);
    }

    /**
     * Obtém a receita mensal global para um ano específico (12 valores).
     * 
     * @param int $ano Ano de referência.
     * @return array Lista com 12 floats.
     */
    public function receitaAnual(int $ano): array {
        $stmt = $this->pdo->prepare("
            SELECT MONTH(data_pagamento) AS mes, SUM(valor) AS total
            FROM pagamentos
            WHERE YEAR(data_pagamento) = :ano
            GROUP BY mes
        ");
        $stmt->execute([':ano' => $ano]);
        $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        $valores = array_fill(0, 12, 0.0);
        foreach ($rows as $mes => $total) {
            $valores[(int)$mes - 1] = (float)$total;
        }
        return $valores;
    }

    /**
     * Obtém a receita mensal de um representante para um ano específico.
     * 
     * @param int $ano Ano de referência.
     * @param int $representanteId ID do representante.
     * @return array Lista com 12 floats.
     */
    public function receitaAnualPorRepresentante(int $ano, int $representanteId): array {
        $stmt = $this->pdo->prepare("
            SELECT MONTH(p.data_pagamento) AS mes, SUM(p.valor) AS total
            FROM pagamentos p
            JOIN clientes c ON p.cliente_id = c.id
            WHERE c.representante_id = :rid AND YEAR(p.data_pagamento) = :ano
            GROUP BY mes
        ");
        $stmt->execute([':rid' => $representanteId, ':ano' => $ano]);
        $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        $valores = array_fill(0, 12, 0.0);
        foreach ($rows as $mes => $total) {
            $valores[(int)$mes - 1] = (float)$total;
        }
        return $valores;
    }

    // ==================== CONTAGEM DE PAGAMENTOS (LICENÇAS GERADAS) ====================

    /**
     * Contagem mensal de pagamentos (global).
     * Representa a quantidade de licenças geradas/renovadas no mês.
     * 
     * @return array Lista com 12 inteiros, de Janeiro a Dezembro do ano corrente.
     */
    public function contagemMensalGlobal(): array {
        $ano = date('Y');
        $stmt = $this->pdo->prepare("
            SELECT MONTH(data_pagamento) AS mes, COUNT(*) AS total
            FROM pagamentos
            WHERE YEAR(data_pagamento) = :ano
            GROUP BY mes
        ");
        $stmt->execute([':ano' => $ano]);
        $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        $valores = array_fill(0, 12, 0);
        foreach ($rows as $mes => $total) {
            $valores[(int)$mes - 1] = (int)$total;
        }
        return $valores;
    }

    /**
     * Contagem mensal de pagamentos de um representante.
     * 
     * @param int $representanteId ID do representante.
     * @return array Lista com 12 inteiros.
     */
    public function contagemMensalPorRepresentante(int $representanteId): array {
        $ano = date('Y');
        $stmt = $this->pdo->prepare("
            SELECT MONTH(p.data_pagamento) AS mes, COUNT(*) AS total
            FROM pagamentos p
            JOIN clientes c ON p.cliente_id = c.id
            WHERE YEAR(p.data_pagamento) = :ano AND c.representante_id = :rid
            GROUP BY mes
        ");
        $stmt->execute([':ano' => $ano, ':rid' => $representanteId]);
        $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        $valores = array_fill(0, 12, 0);
        foreach ($rows as $mes => $total) {
            $valores[(int)$mes - 1] = (int)$total;
        }
        return $valores;
    }
}