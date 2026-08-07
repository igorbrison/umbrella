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

        // --- Clientes ativos/inativos (com detalhes para modal) ---
        $clientes = $clienteModel->listarPorRepresentante($representanteId);
        $totalClientes = count($clientes);
        $ativos = 0;
        $clientesDetalhes = [];
        foreach ($clientes as $c) {
            if ($c['ativo']) $ativos++;
            $clientesDetalhes[] = [
                'nome' => $c['nome'],
                'ativo' => $c['ativo']
            ];
        }
        $dados['clientes_ativos'] = $ativos;
        $dados['clientes_inativos'] = $totalClientes - $ativos;
        $dados['clientes_detalhes'] = $clientesDetalhes;

        // --- Licenças (ativas/expiradas) com detalhes ---
        $licencas = $licencaModel->listarPorRepresentante($representanteId);
        $licencasAtivas = 0;
        $licencasDetalhes = [];
        $hoje = new DateTime();
        foreach ($licencas as $l) {
            $exp = new DateTime($l['data_expiracao']);
            $ativa = $l['ativa'] && $exp >= $hoje;
            if ($ativa) $licencasAtivas++;
            $licencasDetalhes[] = [
                'cliente_nome' => $l['cliente_nome'],
                'ativa' => $ativa
            ];
        }
        $dados['licencas_ativas'] = $licencasAtivas;
        $dados['licencas_expiradas'] = count($licencas) - $licencasAtivas;
        $dados['licencas_detalhes'] = $licencasDetalhes;

        // --- Clientes em atraso (licenças expiradas) detalhes ---
        $atrasoDetalhes = [];
        foreach ($licencas as $l) {
            $exp = new DateTime($l['data_expiracao']);
            if (!$l['ativa'] || $exp < $hoje) {
                $atrasoDetalhes[] = ['cliente_nome' => $l['cliente_nome']];
            }
        }
        $dados['clientes_em_atraso'] = count($atrasoDetalhes);
        $dados['atraso_detalhes'] = $atrasoDetalhes;

        // --- Meses em português (últimos 12) e dados para gráficos ---
        $mesesAbrev = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun',
                       'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        $meses = [];
        $receitaMensal = [];
        $comissaoMensal = [];
        $licencasGeradas = [];
        $maxReceita = 0;
        $maxComissao = 0;
        for ($i = 11; $i >= 0; $i--) {
            $data = new DateTime("first day of -$i months");
            $mesLabel = $data->format('Y-m');
            $mesNum = (int)$data->format('m') - 1; // 0-based
            $meses[] = $mesesAbrev[$mesNum] . '/' . $data->format('y'); // "Jan/25"
            
            $soma = $pagamentoModel->somaPorMes($representanteId, $mesLabel);
            $receitaMensal[] = $soma;
            if ($soma > $maxReceita) $maxReceita = $soma;

            $comissao = round($soma * ($representante['comissao_percentual'] / 100), 2);
            $comissaoMensal[] = $comissao;
            if ($comissao > $maxComissao) $maxComissao = $comissao;

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

        // Escalas dinâmicas (com margem de 20%)
        $dados['maxReceita'] = $maxReceita > 0 ? ceil($maxReceita * 1.2) : 1000;
        $dados['maxComissao'] = $maxComissao > 0 ? ceil($maxComissao * 1.2) : 1000;
        $dados['maxLicencas'] = !empty($licencasGeradas) ? ceil(max($licencasGeradas) * 1.2) : 5;

        // --- Receita total do mês atual ---
        $mesAtual = date('Y-m');
        $dados['receita_total'] = $pagamentoModel->somaPorMes($representanteId, $mesAtual);
        $dados['comissao_total'] = round($dados['receita_total'] * ($representante['comissao_percentual'] / 100), 2);

        // --- Comparativo anual (parâmetros via GET) ---
        $ano1 = (int)($_GET['ano1'] ?? date('Y') - 1);
        $ano2 = (int)($_GET['ano2'] ?? date('Y'));
        $dados['ano1'] = $ano1;
        $dados['ano2'] = $ano2;

        $receitaAnual1 = [];
        $receitaAnual2 = [];
        $maxComparativo = 0;
        for ($m = 1; $m <= 12; $m++) {
            $mes = str_pad($m, 2, '0', STR_PAD_LEFT);
            $val1 = $pagamentoModel->somaPorMes($representanteId, "$ano1-$mes");
            $val2 = $pagamentoModel->somaPorMes($representanteId, "$ano2-$mes");
            $receitaAnual1[] = $val1;
            $receitaAnual2[] = $val2;
            if ($val1 > $maxComparativo) $maxComparativo = $val1;
            if ($val2 > $maxComparativo) $maxComparativo = $val2;
        }
        $dados['receitaAnual1'] = $receitaAnual1;
        $dados['receitaAnual2'] = $receitaAnual2;
        $dados['maxComparativo'] = $maxComparativo > 0 ? ceil($maxComparativo * 1.2) : 1000;
        $dados['comparativoAnos'] = [$ano1, $ano2];

        // Anos disponíveis para seleção (últimos 5 anos)
        $anoAtual = (int)date('Y');
        $dados['anosDisponiveis'] = range($anoAtual - 4, $anoAtual);

        require __DIR__ . '/../Views/dashboard/representante.php';
    }
}