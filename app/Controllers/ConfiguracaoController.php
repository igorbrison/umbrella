<?php
/**
 * Arquivo: Controllers/ConfiguracaoController.php
 * Função: Controlador de configurações globais do sistema (painel admin).
 * 
 * Responsável por:
 *   - Exibir o formulário de configuração do salário mínimo.
 *   - Processar a atualização do valor do salário mínimo.
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
     * Exibe a view com o formulário de configuração do salário mínimo.
     * O valor atual é obtido do banco de dados e passado para a view.
     */
    public function index(): void {
        $salarioMinimo = $this->model->getSalarioMinimo();
        require __DIR__ . '/../Views/admin/configuracao/form.php';
    }

    // ============================================================
    // 2. PROCESSAR ATUALIZAÇÃO
    // ============================================================
    /**
     * Processa o formulário de atualização do salário mínimo.
     * Recebe o novo valor via POST, converte para float e persiste no banco.
     * Após salvar, redireciona de volta para a tela de configuração.
     */
    public function salvar(): void {
        $valor = (float) $_POST['salario_minimo'];
        $this->model->setSalarioMinimo($valor);
        header('Location: /admin/configuracao');
        exit;
    }
}