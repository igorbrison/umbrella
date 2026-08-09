<?php
/**
 * Arquivo: Controllers/DashboardController.php
 * Função: Controlador do Dashboard.
 * 
 * Responsável por:
 *   - Exibir indicadores e gráficos de acordo com o perfil do usuário logado.
 *   - Para administradores: dados consolidados de todos os representantes.
 *   - Para representantes: dados restritos aos seus próprios clientes.
 *   - Preparar os arrays necessários para os gráficos do Chart.js.
 * 
 * Acesso: Rotas protegidas por verificação de sessão no próprio controlador.
 */

require_once __DIR__ . '/../Models/Cliente.php';
require_once __DIR__ . '/../Models/Licenca.php';
require_once __DIR__ . '/../Models/Pagamento.php';

class DashboardController
{
    /**
     * Ponto de entrada do dashboard.
     * Redireciona para o método adequado conforme o perfil do usuário.
     */
    public function index(): void
    {
        // Verifica se é admin ou representante
        if (isset($_SESSION['admin_id'])) {
            $this->admin();
        } elseif (isset($_SESSION['representante_id'])) {
            $this->representante();
        } else {
            header('Location: /login');
            exit;
        }
    }

    /**
     * Dashboard do administrador.
     * Exibe dados globais de todos os representantes e clientes.
     */
    private function admin(): void
    {
        $clienteModel    = new Cliente();
        $licencaModel    = new Licenca();
        /** @var \Pagamento $pagamentoModel */
        $pagamentoModel  = new Pagamento();

        // Totais gerais
        $clientesAtivos    = $clienteModel->contarAtivos();
        $clientesInativos  = $clienteModel->contarInativos();
        $licencasAtivas    = $licencaModel->contarAtivas();
        $licencasExpiradas = $licencaModel->contarExpiradas();
        $clientesAtraso    = $clienteModel->contarEmAtraso();

        // Receitas e comissões
        $receitaMensal   = $pagamentoModel->receitaMensalGlobal();
        $comissaoMensal  = $pagamentoModel->comissaoMensalGlobal();
        $licencasGeradas = $pagamentoModel->contagemMensalGlobal();  // <- alterado

        // Meses e anos para os gráficos
        $meses           = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
        $ano1            = $_GET['ano1'] ?? date('Y') - 1;
        $ano2            = $_GET['ano2'] ?? date('Y');
        $anosDisponiveis = range(date('Y') - 3, date('Y'));

        // Dados comparativos anuais
        $receitaAnual1 = $pagamentoModel->receitaAnual((int)$ano1);
        $receitaAnual2 = $pagamentoModel->receitaAnual((int)$ano2);

        // Máximos para escala dos gráficos
        $maxReceita     = max(max($receitaMensal ?: [0]), 1000);
        $maxComissao    = max(max($comissaoMensal ?: [0]), 1000);
        $maxLicencas    = max(max($licencasGeradas ?: [0]), 5);
        $maxComparativo = max(max($receitaAnual1 ?: [0]), max($receitaAnual2 ?: [0]), 1000);

        // Empacota tudo em um array para a view
        $dados = [
            'clientes_ativos'      => $clientesAtivos,
            'clientes_inativos'    => $clientesInativos,
            'licencas_ativas'      => $licencasAtivas,
            'licencas_expiradas'   => $licencasExpiradas,
            'clientes_em_atraso'   => $clientesAtraso,
            'receitaMensal'        => $receitaMensal,
            'comissaoMensal'       => $comissaoMensal,
            'licencasGeradas'      => $licencasGeradas,
            'meses'                => $meses,
            'ano1'                 => $ano1,
            'ano2'                 => $ano2,
            'anosDisponiveis'      => $anosDisponiveis,
            'receitaAnual1'        => $receitaAnual1,
            'receitaAnual2'        => $receitaAnual2,
            'maxReceita'           => $maxReceita,
            'maxComissao'          => $maxComissao,
            'maxLicencas'          => $maxLicencas,
            'maxComparativo'       => $maxComparativo,
            'clientes_detalhes'    => $clienteModel->listarParaDetalhes(),
            'licencas_detalhes'    => $licencaModel->listarParaDetalhes(),
            'atraso_detalhes'      => $clienteModel->listarEmAtraso(),
        ];

        require __DIR__ . '/../Views/dashboard/admin.php';
    }

    /**
     * Dashboard do representante.
     * Exibe apenas os dados vinculados ao representante logado.
     */
    private function representante(): void
    {
        $repId = $_SESSION['representante_id'];

        $clienteModel   = new Cliente();
        $licencaModel   = new Licenca();
        /** @var \Pagamento $pagamentoModel */
        $pagamentoModel = new Pagamento();

        // Totais do representante
        $clientesAtivos    = $clienteModel->contarAtivosPorRepresentante($repId);
        $clientesInativos  = $clienteModel->contarInativosPorRepresentante($repId);
        $licencasAtivas    = $licencaModel->contarAtivasPorRepresentante($repId);
        $licencasExpiradas = $licencaModel->contarExpiradasPorRepresentante($repId);
        $clientesAtraso    = $clienteModel->contarEmAtrasoPorRepresentante($repId);

        // Receitas e comissões do representante
        $receitaMensal   = $pagamentoModel->receitaMensalPorRepresentante($repId);
        $comissaoMensal  = $pagamentoModel->comissaoMensalPorRepresentante($repId);
        $licencasGeradas = $pagamentoModel->contagemMensalPorRepresentante($repId);  // <- alterado

        // Meses e anos
        $meses           = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
        $ano1            = $_GET['ano1'] ?? date('Y') - 1;
        $ano2            = $_GET['ano2'] ?? date('Y');
        $anosDisponiveis = range(date('Y') - 3, date('Y'));

        // Comparativo anual
        $receitaAnual1 = $pagamentoModel->receitaAnualPorRepresentante((int)$ano1, $repId);
        $receitaAnual2 = $pagamentoModel->receitaAnualPorRepresentante((int)$ano2, $repId);

        // Máximos para gráficos
        $maxReceita     = max(max($receitaMensal ?: [0]), 1000);
        $maxComissao    = max(max($comissaoMensal ?: [0]), 1000);
        $maxLicencas    = max(max($licencasGeradas ?: [0]), 5);
        $maxComparativo = max(max($receitaAnual1 ?: [0]), max($receitaAnual2 ?: [0]), 1000);

        // Array para a view
        $dados = [
            'clientes_ativos'      => $clientesAtivos,
            'clientes_inativos'    => $clientesInativos,
            'licencas_ativas'      => $licencasAtivas,
            'licencas_expiradas'   => $licencasExpiradas,
            'clientes_em_atraso'   => $clientesAtraso,
            'receitaMensal'        => $receitaMensal,
            'comissaoMensal'       => $comissaoMensal,
            'licencasGeradas'      => $licencasGeradas,
            'meses'                => $meses,
            'ano1'                 => $ano1,
            'ano2'                 => $ano2,
            'anosDisponiveis'      => $anosDisponiveis,
            'receitaAnual1'        => $receitaAnual1,
            'receitaAnual2'        => $receitaAnual2,
            'maxReceita'           => $maxReceita,
            'maxComissao'          => $maxComissao,
            'maxLicencas'          => $maxLicencas,
            'maxComparativo'       => $maxComparativo,
            'clientes_detalhes'    => $clienteModel->listarPorRepresentante($repId),
            'licencas_detalhes'    => $licencaModel->listarPorRepresentanteDetalhes($repId),
            'atraso_detalhes'      => $clienteModel->listarEmAtrasoPorRepresentante($repId),
        ];

        require __DIR__ . '/../Views/dashboard/representante.php';
    }
}