<?php
/**
 * Arquivo: Controllers/AdminLicencaController.php
 * Função: Controlador de gerenciamento de licenças pelo administrador.
 * 
 * Responsável por:
 *   - Listar todas as licenças do sistema (todos os clientes e representantes).
 *   - Exibir o valor total atualizado de cada licença (calculado dinamicamente).
 *   - Renovar licenças manualmente (gerar nova chave e nova data de expiração).
 *   - Gerar tokens offline para clientes (para ativação manual sem internet).
 * 
 * Acesso: Rotas protegidas pelo middleware AuthAdminMiddleware.
 */

require_once __DIR__ . '/../Models/Licenca.php';
require_once __DIR__ . '/../Models/TokenRenovacao.php';
require_once __DIR__ . '/../Models/Cliente.php';   // necessário para getValorTotalAtual

class AdminLicencaController {
    
    /**
     * @var Licenca $licencaModel
     * Instância do Model Licenca para operações de banco de dados.
     */
    private Licenca $licencaModel;

    /**
     * Construtor da classe.
     * Inicializa o Model Licenca.
     */
    public function __construct() {
        $this->licencaModel = new Licenca();
    }

    // ============================================================
    // 1. LISTAR TODAS AS LICENÇAS
    // ============================================================
    /**
     * Lista todas as licenças do sistema (visão do administrador).
     * Inclui o nome do cliente, CPF/CNPJ, nome do representante,
     * chave (parcial), data de expiração, valor total e status.
     * 
     * O valor total é calculado dinamicamente com base no salário mínimo
     * vigente e nos módulos contratados por cada cliente.
     */
    public function index(): void {
        // Obtém todas as licenças com dados auxiliares (cliente, representante)
        $licencas = $this->licencaModel->listarTodas();

        // Calcula o valor total atualizado para cada licença
        $clienteModel = new Cliente();
        foreach ($licencas as &$l) {
            $l['valor_total_atual'] = $clienteModel->getValorTotalAtual((int)$l['cliente_id']);
        }

        // Carrega a view de listagem (admin)
        require __DIR__ . '/../Views/admin/licencas/listar.php';
    }

    // ============================================================
    // 2. RENOVAR LICENÇA
    // ============================================================
    /**
     * Renova a licença de um cliente específico.
     * Gera uma nova chave de licença e atualiza a data de expiração
     * (calculada automaticamente para o próximo dia 5).
     * 
     * @param int $clienteId ID do cliente cuja licença será renovada.
     */
    public function renovar(int $clienteId): void {
        $chave = $this->licencaModel->gerarChave();
        $this->licencaModel->criarOuAtualizar($clienteId, $chave);
        header('Location: /admin/licencas');
        exit;
    }

    // ============================================================
    // 3. GERAR TOKEN OFFLINE
    // ============================================================
    /**
     * Gera um token offline para um cliente.
     * O token contém os dados da licença e módulos contratados,
     * permitindo ativação manual pelo cliente no aplicativo Flutter.
     * 
     * O token gerado é armazenado na sessão ($_SESSION['token_gerado'])
     * para ser exibido na view logo após o redirecionamento.
     * Em caso de erro (ex.: cliente sem licença ativa), a mensagem
     * é armazenada em $_SESSION['erro_token'].
     * 
     * @param int $clienteId ID do cliente para o qual o token será gerado.
     */
    public function gerarTokenOffline(int $clienteId): void {
        $tokenModel = new TokenRenovacao();
        try {
            $token = $tokenModel->gerarTokenOffline($clienteId);
            $_SESSION['token_gerado'] = $token;
        } catch (\Exception $e) {
            $_SESSION['erro_token'] = $e->getMessage();
        }
        header('Location: /admin/licencas');
        exit;
    }
}