<?php
require_once __DIR__ . '/../Models/Licenca.php';

class LicencaController {
    private Licenca $licencaModel;
    private int $representanteId;

    public function __construct() {
        if (!isset($_SESSION['representante_id'])) {
            header('Location: /login');
            exit;
        }
        $this->licencaModel = new Licenca();
        $this->representanteId = $_SESSION['representante_id'];
    }

    // Lista as licenças de todos os clientes do representante (apenas visualização)
    public function index(): void {
        $licencas = $this->licencaModel->listarPorRepresentante($this->representanteId);
        require __DIR__ . '/../Views/painel/licencas/listar.php';
    }
}