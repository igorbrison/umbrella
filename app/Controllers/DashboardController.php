<?php
/**
 * Arquivo: Controllers/DashboardController.php
 * Função: Controlador da página inicial do dashboard.
 * 
 * Responsável por:
 *   - Exibir o dashboard do administrador (cards simples).
 *   - Exibir o dashboard do representante com gráficos e indicadores.
 * 
 * Uso: Rota associada → GET /dashboard
 */

require_once __DIR__ . '/../Models/Cliente.php';
require_once __DIR__ . '/../Models/Licenca.php';
require_once __DIR__ . '/../Models/Representante.php';
require_once __DIR__ . '/../Models/ClienteModulo.php';
require_once __DIR__ . '/../Models/Configuracao.php';
require_once __DIR__ . '/../Models/Database.php';
require_once __DIR__ . '/../Models/Pagamento.php';
use Pagamento;

class DashboardController {

    public function index(): void {
        // Administrador: dados básicos
        if (isset($_SESSION['admin_id'])) {
            $dados = [];
            $dados['totalRepresentantes'] = count((new Representante())->listarTodos());
            $licencaModel = new Licenca();
            $dados['totalClientes'] = count($licencaModel->listarTodas());
            $dados['receitaMensal'] = 0;
            $dados['clientesEmAtraso'] = 0;
            require __DIR__ . '/../Views/dashboard/admin.php';
            return;
        }

        // Representante
        if (!isset($_SESSION['representante_id'])) {
            header('Location: /login');
            exit;
        }

        $representanteId = $_SESSION['representante_id'];
        $clienteModel = new Cliente();
        $licencaModel = new Licenca();
        $representanteModel = new Representante();
        $pagamentoModel = new Pagamento();
        $representante = $representanteModel->buscarPorId($representanteId);

        // --- Clientes ativos/inativos ---
        $clientes = $clienteModel->listarPorRepresentante($representanteId);
        $totalClientes = count($clientes);
        $ativos = 0;
        foreach ($clientes as $c) {
            if ($c['ativo']) $ativos++;
        }
        $dados['clientes_ativos'] = $ativos;
        $dados['clientes_inativos'] = $totalClientes - $ativos;

        // --- Licenças (ativas/expiradas) ---
        $statusLicencas = $licencaModel->contarPorStatus($representanteId);
        $dados['licencas_ativas'] = (int)$statusLicencas['ativas'];
        $dados['licencas_expiradas'] = (int)$statusLicencas['expiradas'];

        // --- Receita e comissão dos últimos 12 meses ---
        $meses = [];
        $receitaMensal = [];
        $comissaoMensal = [];
        $licencasGeradas = [];
        for ($i = 11; $i >= 0; $i--) {
            $data = new DateTime("first day of -$i months");
            $mesLabel = $data->format('Y-m');
            $meses[] = $data->format('M/Y');
            
            $soma = $pagamentoModel->somaPorMes($representanteId, $mesLabel);
            $receitaMensal[] = $soma;
            $comissaoMensal[] = round($soma * ($representante['comissao_percentual'] / 100), 2);

            // Licenças geradas no mês
            $stmt = Database::getInstance()->prepare(
                "SELECT COUNT(*) FROM licencas l
                 JOIN clientes c ON l.cliente_id = c.id
                 WHERE c.representante_id = :rid AND DATE_FORMAT(l.criada_em, '%Y-%m') = :ym"
            );
            $stmt->execute([':rid' => $representanteId, ':ym' => $mesLabel]);
            $licencasGeradas[] = (int)$stmt->fetchColumn();
        }

        $dados['meses'] = $meses;
        $dados['receitaMensal'] = $receitaMensal;
        $dados['comissaoMensal'] = $comissaoMensal;
        $dados['licencasGeradas'] = $licencasGeradas;

        // --- Receita total do mês atual ---
        $mesAtual = date('Y-m');
        $dados['receita_total'] = $pagamentoModel->somaPorMes($representanteId, $mesAtual);
        $dados['comissao_total'] = round($dados['receita_total'] * ($representante['comissao_percentual'] / 100), 2);

        // --- Clientes em atraso (licenças expiradas) ---
        $dados['clientes_em_atraso'] = $dados['licencas_expiradas'];

        // --- Comparativo anual (placeholder com dados simulados) ---
        $anoAtual = date('Y');
        $anoAnterior = $anoAtual - 1;
        $dados['comparativoAnos'] = [$anoAnterior, $anoAtual];
        $dados['receitaAnual1'] = array_map(function(){ return rand(800,4000); }, range(1,12));
        $dados['receitaAnual2'] = array_map(function(){ return rand(800,4000); }, range(1,12));

        require __DIR__ . '/../Views/dashboard/representante.php';
    }
}