<?php
/**
 * Controlador de Relatórios (Admin e Representante).
 */
require_once __DIR__ . '/../Models/Pagamento.php';
require_once __DIR__ . '/../Models/Representante.php';

class RelatorioController
{
    private Pagamento $pagamentoModel;

    public function __construct()
    {
        $this->pagamentoModel = new Pagamento();
    }

    /**
     * Relatório de pagamentos para o admin.
     */
    public function admin(): void
    {
        if (!isset($_SESSION['admin_id'])) {
            header('Location: /login');
            exit;
        }

        $dataInicio = $_GET['data_inicio'] ?? null;
        $dataFim = $_GET['data_fim'] ?? null;
        $representanteId = $_GET['representante_id'] ?? null;
        $termo = $_GET['termo'] ?? '';
        $pagina = (int)($_GET['pagina'] ?? 1);

        $resultado = $this->pagamentoModel->listarParaRelatorio(
            $dataInicio, $dataFim,
            $representanteId ? (int)$representanteId : null,
            $termo, $pagina, 12          // ← 12 por página
        );

        $representantes = (new Representante())->listarComOrdenacao('nome_razao', 'asc');

        $dados = [
            'pagamentos'      => $resultado['dados'],
            'total'           => $resultado['total'],
            'somaPeriodo'     => $resultado['soma_periodo'],
            'representantes'  => $representantes,
            'paginaAtual'     => $pagina,
            'totalPaginas'    => ceil($resultado['total'] / 12),
            'dataInicio'      => $dataInicio,
            'dataFim'         => $dataFim,
            'representanteId' => $representanteId,
            'termo'           => $termo,
        ];

        $titulo = 'Relatório de Pagamentos';
        require __DIR__ . '/../Views/admin/relatorios/pagamentos.php';
    }

    /**
     * Relatório de pagamentos para o representante.
     */
    public function painel(): void
    {
        if (!isset($_SESSION['representante_id'])) {
            header('Location: /login');
            exit;
        }

        $dataInicio = $_GET['data_inicio'] ?? null;
        $dataFim = $_GET['data_fim'] ?? null;
        $termo = $_GET['termo'] ?? '';
        $pagina = (int)($_GET['pagina'] ?? 1);

        $resultado = $this->pagamentoModel->listarParaRelatorio(
            $dataInicio, $dataFim,
            $_SESSION['representante_id'],
            $termo, $pagina, 12          // ← 12 por página
        );

        $dados = [
            'pagamentos'   => $resultado['dados'],
            'total'        => $resultado['total'],
            'somaPeriodo'  => $resultado['soma_periodo'],
            'paginaAtual'  => $pagina,
            'totalPaginas' => ceil($resultado['total'] / 12),
            'dataInicio'   => $dataInicio,
            'dataFim'      => $dataFim,
            'termo'        => $termo,
        ];

        $titulo = 'Relatório de Pagamentos';
        require __DIR__ . '/../Views/painel/relatorios/pagamentos.php';
    }
}