<?php
/**
 * Arquivo: Controllers/LicencaController.php
 * Função: Controlador de visualização de licenças (painel do representante).
 * 
 * Responsável por:
 *   - Listar as licenças dos clientes vinculados ao representante logado.
 *   - Exibir informações como: nome do cliente, CPF/CNPJ, chave (parcial),
 *     data de expiração (com alerta a partir do dia 28), valor total e status.
 * 
 * O representante NÃO pode realizar ações de renovação ou geração de token
 * offline nesta tela. Essas funcionalidades foram movidas para o
 * AdminLicencaController (painel do administrador).
 * 
 * Acesso: Rotas protegidas pelo middleware inline no grupo /painel.
 */

require_once __DIR__ . '/../Models/Licenca.php';

class LicencaController {
    
    /**
     * @var Licenca $licencaModel
     * Instância do Model Licenca para operações de banco de dados.
     */
    private Licenca $licencaModel;
    
    /**
     * @var int $representanteId
     * ID do representante logado (obtido da sessão).
     */
    private int $representanteId;

    /**
     * Construtor da classe.
     * Verifica a autenticação do representante e inicializa o Model.
     * Redireciona para /login se não houver sessão ativa.
     */
    public function __construct() {
        if (!isset($_SESSION['representante_id'])) {
            header('Location: /login');
            exit;
        }
        $this->licencaModel = new Licenca();
        $this->representanteId = $_SESSION['representante_id'];
    }

    // ============================================================
    // 1. LISTAR LICENÇAS DO REPRESENTANTE
    // ============================================================
    /**
     * Lista as licenças de todos os clientes vinculados ao representante logado.
     * A view exibe apenas informações de consulta, sem botões de ação
     * (renovação e token offline são exclusivos do administrador).
     */
    public function index(): void {
        $licencas = $this->licencaModel->listarPorRepresentante($this->representanteId);
        require __DIR__ . '/../Views/painel/licencas/listar.php';
    }
}