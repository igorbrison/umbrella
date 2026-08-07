<?php
/**
 * Arquivo: Controllers/SolicitacaoController.php
 * Função: Controlador de solicitações do representante.
 * 
 * Responsável por:
 *   - Exibir a lista de solicitações do representante logado.
 *   - Exibir o formulário para envio de novas solicitações.
 *   - Processar o envio de novas solicitações (título e descrição).
 * 
 * Fluxo:
 *   1. Representante acessa /painel/solicitacoes e vê suas solicitações
 *      anteriores mais o formulário para nova solicitação.
 *   2. Ao enviar o formulário, a solicitação é salva no banco com
 *      status inicial "pendente".
 *   3. O administrador posteriormente avalia e atualiza o status.
 * 
 * Acesso: Rotas protegidas pelo middleware inline no grupo /painel.
 */

require_once __DIR__ . '/../Models/Solicitacao.php';

class SolicitacaoController {
    
    /**
     * @var Solicitacao $model
     * Instância do Model Solicitacao para operações de banco de dados.
     */
    private Solicitacao $model;
    
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
        $this->model = new Solicitacao();
        $this->representanteId = $_SESSION['representante_id'];
    }

    // ============================================================
    // 1. LISTAR SOLICITAÇÕES E EXIBIR FORMULÁRIO
    // ============================================================
    /**
     * Exibe a lista de solicitações do representante logado
     * e o formulário para envio de uma nova solicitação.
     * 
     * Se houver uma mensagem de sucesso na sessão (após envio),
     * ela é exibida e depois removida.
     */
    public function index(): void {
        // Busca as solicitações do representante
        $solicitacoes = $this->model->listarPorRepresentante($this->representanteId);
        
        // Mensagem de sucesso (se existir)
        $sucesso = $_SESSION['sucesso_solicitacao'] ?? null;
        unset($_SESSION['sucesso_solicitacao']);
        
        // Carrega a view
        require __DIR__ . '/../Views/painel/solicitacoes/index.php';
    }

    // ============================================================
    // 2. PROCESSAR ENVIO DE NOVA SOLICITAÇÃO
    // ============================================================
    /**
     * Processa o formulário de envio de uma nova solicitação.
     * 
     * Valida se os campos obrigatórios (título e descrição) foram preenchidos.
     * Se sim, insere a solicitação no banco com status inicial "pendente".
     * Armazena uma mensagem de sucesso na sessão e redireciona para a listagem.
     */
    public function enviar(): void {
        // Captura os dados do formulário
        $titulo = $_POST['titulo'] ?? '';
        $descricao = $_POST['descricao'] ?? '';
        
        // Valida campos obrigatórios
        if (empty($titulo) || empty($descricao)) {
            echo "Preencha todos os campos.";
            exit;
        }
        
        // Insere a nova solicitação no banco
        $this->model->inserir($this->representanteId, $titulo, $descricao);
        
        // Mensagem de sucesso e redirecionamento
        $_SESSION['sucesso_solicitacao'] = 'Solicitação enviada com sucesso!';
        header('Location: /painel/solicitacoes');
        exit;
    }
}