<?php
/**
 * Arquivo: Controllers/DashboardController.php
 * Função: Controlador da página inicial do dashboard.
 * 
 * Responsável por redirecionar o usuário autenticado para a
 * página inicial correta conforme seu perfil:
 *   - Administrador → /admin/representantes
 *   - Representante → /painel/clientes
 * 
 * Se nenhum usuário estiver logado, redireciona para /login.
 * 
 * Uso: Rota associada → GET /dashboard
 */
require_once __DIR__ . '/../Models/Cliente.php';
require_once __DIR__ . '/../Models/Licenca.php';
require_once __DIR__ . '/../Models/Representante.php';

class DashboardController {
    public function index(): void {
        $dados = [];

        if (isset($_SESSION['admin_id'])) {
            // Admin
            $clienteModel = new Cliente();
            $licencaModel = new Licenca();
            $dados['totalClientes'] = count($licencaModel->listarTodas()); // total de licenças = clientes
            $dados['totalRepresentantes'] = count((new Representante())->listarTodos());
            // Placeholders para outros cards
            $dados['receitaMensal'] = 0;
            $dados['clientesEmAtraso'] = 0;
            require __DIR__ . '/../Views/dashboard/admin.php';
        } elseif (isset($_SESSION['representante_id'])) {
            // Representante
            $representanteId = $_SESSION['representante_id'];
            $clienteModel = new Cliente();
            $clientes = $clienteModel->listarPorRepresentante($representanteId);
            $dados['totalClientes'] = count($clientes);
            // Calcula licenças próximas do vencimento (exemplo: até 7 dias)
            $licencaModel = new Licenca();
            $licencas = $licencaModel->listarPorRepresentante($representanteId);
            $proximasVencer = 0;
            $hoje = new DateTime();
            foreach ($licencas as $l) {
                $exp = new DateTime($l['data_expiracao']);
                $diff = (int)$hoje->diff($exp)->format('%r%a');
                if ($diff <= 7 && $diff >= 0) {
                    $proximasVencer++;
                }
            }
            $dados['proximasVencer'] = $proximasVencer;
            $dados['receitaMensal'] = 0; // placeholder
            require __DIR__ . '/../Views/dashboard/representante.php';
        } else {
            header('Location: /login');
            exit;
        }
    }
}