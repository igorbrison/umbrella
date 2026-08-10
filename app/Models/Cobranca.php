<?php
/**
 * Arquivo: Models/Cobranca.php
 * Função: Model para gerenciar cobranças automáticas.
 * 
 * Responsável por:
 *   - Listar clientes que devem receber cobrança hoje.
 *   - Verificar se já foi enviada cobrança para um cliente no mês atual.
 *   - Registrar log de cobrança enviada.
 */

require_once __DIR__ . '/Database.php';

class Cobranca
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    /**
     * Retorna os clientes que devem ser cobrados hoje.
     * 
     * Lógica: a licença expira no próximo dia 5 e hoje é exatamente
     * o dia (5 - dias_antecedencia). O padrão é 3 dias antes → dia 2.
     * 
     * @param int $diasAntecedencia Quantos dias antes do vencimento enviar.
     * @return array Lista de clientes com email, nome, representante, etc.
     */
    public function clientesParaCobrarHoje(int $diasAntecedencia = 3): array
    {
        $hoje = new DateTime();
        $diaHoje = (int)$hoje->format('d');
        $diaCobranca = 5 - $diasAntecedencia;

        // Só envia se hoje for exatamente o dia calculado
        if ($diaHoje !== $diaCobranca) {
            return [];
        }

        $anoMes = $hoje->format('Y-m');

        // Clientes ativos com licença que expira no dia 5 do mês atual
        // e que NÃO receberam cobrança neste mês (mes_referencia = ano-mes)
        $sql = "SELECT c.id, c.nome, c.email, r.nome_razao AS representante_nome, r.email AS representante_email,
                       l.data_expiracao, c.valor_total
                FROM clientes c
                JOIN licencas l ON l.cliente_id = c.id AND l.ativa = 1
                JOIN representantes r ON r.id = c.representante_id
                WHERE c.ativo = 1
                  AND l.data_expiracao = :data_expiracao
                  AND c.id NOT IN (
                      SELECT cliente_id FROM logs_cobranca
                      WHERE mes_referencia = :mes_ref AND sucesso = 1
                  )
                ORDER BY c.nome";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':data_expiracao' => $hoje->format('Y-m-05'),
            ':mes_ref' => $anoMes,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Registra que uma cobrança foi enviada para um cliente.
     */
    public function registrarLog(int $clienteId, string $mesReferencia, bool $sucesso, string $mensagem = null): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO logs_cobranca (cliente_id, mes_referencia, sucesso, mensagem) VALUES (:cid, :mes, :sucesso, :msg)"
        );
        $stmt->execute([
            ':cid' => $clienteId,
            ':mes' => $mesReferencia,
            ':sucesso' => $sucesso ? 1 : 0,
            ':msg' => $mensagem,
        ]);
    }
}