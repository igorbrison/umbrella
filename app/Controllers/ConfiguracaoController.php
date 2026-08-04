<?php
require_once __DIR__ . '/../Models/Configuracao.php';

class ConfiguracaoController {
    private Configuracao $model;

    public function __construct() {
        $this->model = new Configuracao();
    }

    public function index(): void {
        $salarioMinimo = $this->model->getSalarioMinimo();
        require __DIR__ . '/../Views/admin/configuracao/form.php';
    }

    public function salvar(): void {
        $valor = (float) $_POST['salario_minimo'];
        $this->model->setSalarioMinimo($valor);
        header('Location: /admin/configuracao');
        exit;
    }
}