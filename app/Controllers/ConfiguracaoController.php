<?php
/**
 * Arquivo: Controllers/ConfiguracaoController.php
 * Função: Controlador de configurações globais do sistema (painel admin).
 * 
 * Responsável por:
 *   - Exibir o formulário de configuração completo (salário mínimo, identidade da empresa, SMTP).
 *   - Processar a atualização de todas as configurações.
 * 
 * O salário mínimo é utilizado como base para o cálculo dos preços
 * de todos os módulos contratados pelos clientes.
 * 
 * Acesso: Rotas protegidas pelo middleware AuthAdminMiddleware.
 */

require_once __DIR__ . '/../Models/Configuracao.php';

class ConfiguracaoController {
    
    /**
     * @var Configuracao $model
     * Instância do Model Configuracao para acesso ao banco de dados.
     */
    private Configuracao $model;

    /**
     * Construtor da classe.
     * Inicializa o Model Configuracao.
     */
    public function __construct() {
        $this->model = new Configuracao();
    }

    // ============================================================
    // 1. EXIBIR FORMULÁRIO DE CONFIGURAÇÃO
    // ============================================================
    /**
     * Exibe a view com o formulário completo de configuração.
     * Os valores atuais são obtidos do banco de dados e passados para a view.
     */
    public function index(): void {
        // Carrega todas as configurações disponíveis
        $configs = [
            'salario_minimo' => $this->model->get('salario_minimo') ?? 1621.00,
            'nome_empresa'   => $this->model->get('nome_empresa') ?? 'Umbrella Corporation',
            'email_contato'  => $this->model->get('email_contato') ?? '',
            'smtp_host'      => $this->model->get('smtp_host') ?? '',
            'smtp_port'      => $this->model->get('smtp_port') ?? 587,
            'smtp_user'      => $this->model->get('smtp_user') ?? '',
            'smtp_pass'      => $this->model->get('smtp_pass') ?? '',
        ];

        require __DIR__ . '/../Views/admin/configuracao/form.php';
    }

    // ============================================================
    // 2. PROCESSAR ATUALIZAÇÃO
    // ============================================================
    /**
     * Processa o formulário de atualização das configurações.
     * Recebe os novos valores via POST e persiste cada um no banco.
     * Após salvar, redireciona de volta para a tela de configuração.
     */
    public function salvar(): void {
        // Lista de todas as chaves que podem ser atualizadas
        $campos = [
            'salario_minimo',
            'nome_empresa',
            'email_contato',
            'smtp_host',
            'smtp_port',
            'smtp_user',
            'smtp_pass',
        ];

        foreach ($campos as $chave) {
            if (isset($_POST[$chave])) {
                $this->model->set($chave, $_POST[$chave]);
            }
        }

        header('Location: /admin/configuracao?sucesso=1');
        exit;
    }
}