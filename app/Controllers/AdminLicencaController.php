<?php
/**
 * Arquivo: Controllers/AdminLicencaController.php
 * Função: Controlador de gerenciamento de licenças/clientes pelo administrador.
 * 
 * Responsável por:
 *   - Listar todas as licenças/clientes com busca, ordenação e paginação.
 *   - Renovar licenças manualmente.
 *   - Gerar tokens offline para clientes.
 *   - Registrar pagamentos e renovar licenças automaticamente.
 * 
 * Acesso: Rotas protegidas pelo middleware AuthAdminMiddleware.
 */

require_once __DIR__ . '/../Models/Licenca.php';
require_once __DIR__ . '/../Models/TokenRenovacao.php';
require_once __DIR__ . '/../Models/Cliente.php';
require_once __DIR__ . '/../Models/Pagamento.php';

class AdminLicencaController {
    
    private Licenca $licencaModel;

    public function __construct() {
        $this->licencaModel = new Licenca();
    }

    /**
     * Lista todas as licenças/clientes com busca, ordenação e paginação.
     */
    public function index(): void {
        $pagina = (int)($_GET['pagina'] ?? 1);
        $termo = $_GET['termo'] ?? '';
        $ordem = $_GET['ordem'] ?? 'cliente_nome';
        $direcao = $_GET['direcao'] ?? 'asc';

        $licencas = $this->licencaModel->listarTodasPaginado($pagina, 10, $termo, $ordem, $direcao);
        $total = $this->licencaModel->contarTodas($termo);
        $totalPaginas = ceil($total / 10);

        $paginacao = [
            'pagina_atual' => $pagina,
            'total_paginas' => $totalPaginas,
        ];
        $ordenacaoAtual = ['coluna' => $ordem, 'direcao' => $direcao];
        $termoAtual = $termo;

        // Mensagens de token
        $tokenGerado = $_SESSION['token_gerado'] ?? null;
        $erroToken = $_SESSION['erro_token'] ?? null;
        unset($_SESSION['token_gerado'], $_SESSION['erro_token']);

        require __DIR__ . '/../Views/admin/clientes/listar.php';
    }

    /**
     * Renova a licença de um cliente específico.
     * 
     * @param int $clienteId ID do cliente.
     */
    public function renovar(int $clienteId): void {
        $chave = $this->licencaModel->gerarChave();
        $this->licencaModel->criarOuAtualizar($clienteId, $chave);
        header('Location: /admin/clientes');
        exit;
    }

    /**
     * Gera token offline para um cliente.
     * 
     * @param int $clienteId ID do cliente.
     */
    public function gerarTokenOffline(int $clienteId): void {
        $tokenModel = new TokenRenovacao();
        try {
            $token = $tokenModel->gerarTokenOffline($clienteId);
            $_SESSION['token_gerado'] = $token;
        } catch (\Exception $e) {
            $_SESSION['erro_token'] = $e->getMessage();
        }
        header('Location: /admin/clientes');
        exit;
    }

    /**
     * Registra pagamento e renova a licença do cliente automaticamente.
     */
    public function pagar(): void {
        $clienteId = (int)($_POST['cliente_id'] ?? 0);
        $valor = (float)($_POST['valor'] ?? 0);
        $dataPagamento = $_POST['data_pagamento'] ?? date('Y-m-d');
        $mesReferencia = $_POST['mes_referencia'] ?? date('Y-m');
        $observacao = $_POST['observacao'] ?? null;

        if ($clienteId <= 0 || $valor <= 0) {
            echo "Cliente e valor são obrigatórios.";
            exit;
        }

        $pagamentoModel = new Pagamento();
        $pagamentoModel->inserir($clienteId, $valor, $dataPagamento, $mesReferencia, $observacao);

        // Renova a licença
        $chave = $this->licencaModel->gerarChave();
        $qtdMaquinas = (int)($this->licencaModel->buscarPorCliente($clienteId)['qtd_maquinas'] ?? 1);
        $this->licencaModel->criarOuAtualizar($clienteId, $chave, $qtdMaquinas);

        $_SESSION['token_gerado'] = "Pagamento registrado e licença renovada!";
        header('Location: /admin/clientes');
        exit;
    }
}