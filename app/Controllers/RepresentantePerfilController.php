<?php
/**
 * Arquivo: Controllers/RepresentantePerfilController.php
 * Função: Controlador para edição do próprio perfil do representante.
 * 
 * Permite que o representante edite seus dados cadastrais,
 * exceto módulos contratados e licenças.
 */
require_once __DIR__ . '/../Models/Representante.php';
require_once __DIR__ . '/../Models/Configuracao.php';
use Respect\Validation\Validator as v;

class RepresentantePerfilController {

    private Representante $model;

    public function __construct() {
        if (!isset($_SESSION['representante_id'])) {
            header('Location: /login');
            exit;
        }
        $this->model = new Representante();
    }

    public function editar(): void {
        $representante = $this->model->buscarPorId($_SESSION['representante_id']);
        if (!$representante) {
            echo "Representante não encontrado.";
            exit;
        }
        $configModel = new Configuracao();
        $salarioMinimo = $configModel->getSalarioMinimo();
        require __DIR__ . '/../Views/painel/perfil/form.php';
    }

    public function salvar(): void {
        $representanteAtual = $this->model->buscarPorId($_SESSION['representante_id']);
        if (!$representanteAtual) {
            echo "Representante não encontrado.";
            exit;
        }

        // Bloqueia a edição dos dados principais (exceto nome_exibicao)
        $dados = [
            ':cnpj'                => $representanteAtual['cnpj'],
            ':inscricao_estadual'  => $representanteAtual['inscricao_estadual'] ?? null,
            ':nome_razao'          => $representanteAtual['nome_razao'],
            ':nome_fantasia'       => $representanteAtual['nome_fantasia'] ?? null,
            ':nome_exibicao'       => $_POST['nome_exibicao'] ?? $representanteAtual['nome_exibicao'],
            ':cnae'                => $representanteAtual['cnae'] ?? null,
            ':crt'                 => $representanteAtual['crt'] ?? null,
            ':data_fundacao'       => $representanteAtual['data_fundacao'] ?? null,
            ':comissao_percentual' => $representanteAtual['comissao_percentual'],   // não pode ser alterada
            ':logradouro'          => $_POST['logradouro'] ?? $representanteAtual['logradouro'],
            ':numero'              => $_POST['numero'] ?? $representanteAtual['numero'],
            ':complemento'         => $_POST['complemento'] ?? $representanteAtual['complemento'],
            ':bairro'              => $_POST['bairro'] ?? $representanteAtual['bairro'],
            ':cep'                 => $_POST['cep'] ?? $representanteAtual['cep'],
            ':estado'              => $_POST['estado'] ?? $representanteAtual['estado'],
            ':municipio'           => $_POST['municipio'] ?? $representanteAtual['municipio'],
            ':telefone'            => $_POST['telefone'] ?? $representanteAtual['telefone'],
            ':celular'             => $_POST['celular'] ?? $representanteAtual['celular'],
            ':email'               => $_POST['email'] ?? $representanteAtual['email'],
            ':observacoes'         => $_POST['observacoes'] ?? $representanteAtual['observacoes'],
            ':ativo'               => $representanteAtual['ativo'] ?? 1,
        ];

        $this->model->atualizar($_SESSION['representante_id'], $dados);

        // Atualiza a sessão com os novos valores permitidos
        $_SESSION['representante_nome'] = $dados[':nome_exibicao'] ?: $dados[':nome_razao'];
        $_SESSION['representante_email'] = $dados[':email'];

        $_SESSION['sucesso_perfil'] = 'Perfil atualizado com sucesso!';
        header('Location: /painel/perfil');
        exit;
    }
}