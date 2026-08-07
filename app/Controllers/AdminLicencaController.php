<?php
/**
 * Arquivo: Controllers/AdminLicencaController.php
 * Função: Controlador de gerenciamento de licenças pelo administrador.
 * 
 * Responsável por:
 *   - Listar todas as licenças do sistema.
 *   - Exibir o valor total atualizado de cada licença.
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
use Pagamento;

class AdminLicencaController {
    
    private Licenca $licencaModel;

    public function __construct() {
        $this->licencaModel = new Licenca();
    }

    // Lista todas as licenças do sistema
    public function index(): void {
        $licencas = $this->licencaModel->listarTodas();

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

    // Registrar pagamento e renovar licença
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
        header('Location: /admin/licencas');
        exit;
    }
}