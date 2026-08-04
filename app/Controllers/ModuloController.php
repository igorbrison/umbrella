<?php
require_once __DIR__ . '/../Models/Modulo.php';
require_once __DIR__ . '/../Models/Configuracao.php';

class ModuloController {
    private Modulo $model;

    public function __construct() {
        $this->model = new Modulo();
    }

    public function index(): void {
        $ordem = $_GET['ordem'] ?? 'id';
        $direcao = $_GET['direcao'] ?? 'asc';
        $modulos = $this->model->listarComOrdenacao($ordem, $direcao);
        $ordenacaoAtual = ['coluna' => $ordem, 'direcao' => $direcao];
        require __DIR__ . '/../Views/admin/modulos/listar.php';
    }

    public function criar(): void {
        $modulo = null;
        $configModel = new Configuracao();
        $salarioMinimo = $configModel->getSalarioMinimo();
        require __DIR__ . '/../Views/admin/modulos/form.php';
    }

    public function salvar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/modulos');
            exit;
        }

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

    public function excluir(int $id): void {
        $this->model->excluir($id);
        header('Location: /admin/modulos');
        exit;
    }
}