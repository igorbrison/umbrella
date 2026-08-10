/**
 * Calcula a comissão total de um representante em um período (ou toda se não informado).
 */
public function comissaoPorRepresentante(int $representanteId, ?string $dataInicio = null, ?string $dataFim = null): float
{
    $where = "c.representante_id = :rid";
    $params = [':rid' => $representanteId];
    if ($dataInicio) {
        $where .= " AND p.data_pagamento >= :inicio";
        $params[':inicio'] = $dataInicio;
    }
    if ($dataFim) {
        $where .= " AND p.data_pagamento <= :fim";
        $params[':fim'] = $dataFim;
    }
    $sql = "SELECT COALESCE(SUM(p.valor * r.comissao_percentual / 100), 0)
            FROM pagamentos p
            JOIN clientes c ON p.cliente_id = c.id
            JOIN representantes r ON c.representante_id = r.id
            WHERE $where";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
    return (float)$stmt->fetchColumn();
}