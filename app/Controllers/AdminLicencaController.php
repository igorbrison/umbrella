<?php
require_once __DIR__ . '/../Models/Licenca.php';
require_once __DIR__ . '/../Models/TokenRenovacao.php';
require_once __DIR__ . '/../Models/Cliente.php';   // necessário para getValorTotalAtual

class AdminLicencaController {
    private Licenca $licencaModel;

    public function __construct() {
        $this->licencaModel = new Licenca();
    }

    // Lista todas as licenças do sistema
    public function index(): void {
        $licencas = $this->licencaModel->listarTodas();

        // Calcula o valor total atualizado para cada licença
        $clienteModel = new Cliente();
        foreach ($licencas as &$l) {
            $l['valor_total_atual'] = $clienteModel->getValorTotalAtual((int)$l['cliente_id']);
        }

        require __DIR__ . '/../Views/admin/licencas/listar.php';
    }

    // Renova a licença de um cliente específico
    public function renovar(int $clienteId): void {
        $chave = $this->licencaModel->gerarChave();
        $this->licencaModel->criarOuAtualizar($clienteId, $chave);
        header('Location: /admin/licencas');
        exit;
    }

    // Gera token offline para um cliente
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