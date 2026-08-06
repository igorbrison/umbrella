<?php
/**
 * Arquivo: Controllers/ModuloController.php
 * Função: Controlador de gerenciamento de módulos (painel admin).
 * 
 * Responsável por:
 *   - Listar todos os módulos cadastrados com ordenação clicável.
 *   - Exibir formulários de criação e edição de módulos.
 *   - Processar o cadastro de novos módulos (com percentual do salário mínimo).
 *   - Processar a edição de módulos existentes.
 *   - Excluir módulos do sistema.
 * 
 * Cada módulo representa uma funcionalidade do sistema de automação
 * que pode ser contratada pelos clientes. O preço do módulo é calculado
 * com base em um percentual do salário mínimo vigente.
 * 
 * Acesso: Rotas protegidas pelo middleware AuthAdminMiddleware.
 */

require_once __DIR__ . '/../Models/Modulo.php';
require_once __DIR__ . '/../Models/Configuracao.php';

class ModuloController {
    
    /**
     * @var Modulo $model
     * Instância do Model Modulo para operações de banco de dados.
     */
    private Modulo $model;

    /**
     * Construtor da classe.
     * Inicializa o Model Modulo.
     */
    public function __construct() {
        $this->model = new Modulo();
    }

    // ============================================================
    // 1. LISTAR MÓDULOS
    // ============================================================
    /**
     * Lista todos os módulos cadastrados.
     * Suporta ordenação por coluna clicável (id, identificador, nome, valor, ativo).
     * O valor exibido é calculado dinamicamente com base no percentual e salário mínimo.
     */
    public function index(): void {
        $ordem = $_GET['ordem'] ?? 'id';
        $direcao = $_GET['direcao'] ?? 'asc';
        $modulos = $this->model->listarComOrdenacao($ordem, $direcao);
        $ordenacaoAtual = ['coluna' => $ordem, 'direcao' => $direcao];
        require __DIR__ . '/../Views/admin/modulos/listar.php';
    }

    // ============================================================
    // 2. EXIBIR FORMULÁRIO DE CRIAÇÃO
    // ============================================================
    /**
     * Exibe o formulário em branco para cadastrar um novo módulo.
     * Obtém o salário mínimo atual para exibir o valor calculado na view.
     */
    public function criar(): void {
        $modulo = null;
        $configModel = new Configuracao();
        $salarioMinimo = $configModel->getSalarioMinimo();
        require __DIR__ . '/../Views/admin/modulos/form.php';
    }

    // ============================================================
    // 3. PROCESSAR FORMULÁRIO (CRIAÇÃO OU EDIÇÃO)
    // ============================================================
    /**
     * Processa o envio do formulário de módulo.
     * 
     * Fluxo:
     *   - Se existir 'id' no POST, chama o Model para ATUALIZAR (UPDATE).
     *   - Caso contrário, chama o Model para INSERIR (INSERT).
     *   - Redireciona para a lista de módulos.
     * 
     * Campos esperados:
     *   - identificador: código interno do módulo (ex: vendas, estoque)
     *   - nome: nome amigável do módulo
     *   - percentual: percentual do salário mínimo (define o preço)
     *   - descricao: descrição opcional
     *   - ativo: checkbox (marcado = 1, desmarcado = 0)
     */
    public function salvar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/modulos');
            exit;
        }

        // Monta o array de dados com os campos do formulário
        $dados = [
            ':identificador' => $_POST['identificador'],
            ':nome' => $_POST['nome'],
            ':percentual_salario_minimo' => $_POST['percentual'] ?? null,
            ':descricao' => $_POST['descricao'] ?? null,
            ':ativo' => isset($_POST['ativo']) ? 1 : 0
        ];

        if (!empty($_POST['id'])) {
            $this->model->atualizar((int)$_POST['id'], $dados);
        } else {
            $this->model->inserir($dados);
        }

        header('Location: /admin/modulos');
        exit;
    }

    // ============================================================
    // 4. EXIBIR FORMULÁRIO DE EDIÇÃO
    // ============================================================
    /**
     * Carrega o formulário de edição com os dados atuais do módulo.
     * Obtém o salário mínimo atual para exibir o valor calculado na view.
     * 
     * @param int $id ID do módulo a ser editado.
     */
    public function editar(int $id): void {
        $modulo = $this->model->buscarPorId($id);
        if (!$modulo) {
            echo "Módulo não encontrado.";
            exit;
        }
        $configModel = new Configuracao();
        $salarioMinimo = $configModel->getSalarioMinimo();
        require __DIR__ . '/../Views/admin/modulos/form.php';
    }

    // ============================================================
    // 5. EXCLUIR MÓDULO
    // ============================================================
    /**
     * Exclui permanentemente um módulo do sistema (hard delete).
     * 
     * @param int $id ID do módulo a ser excluído.
     * 
     * ATENÇÃO: Esta operação é irreversível. Considere o impacto nos
     * clientes que possuem este módulo contratado.
     */
    public function excluir(int $id): void {
        $this->model->excluir($id);
        header('Location: /admin/modulos');
        exit;
    }
}