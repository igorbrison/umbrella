<?php
/**
 * Arquivo: Controllers/AdminClienteController.php
 * Função: Controlador de edição de clientes pelo administrador.
 * 
 * Responsável por:
 *   - Permitir que o administrador edite qualquer cliente do sistema.
 *   - Alterar dados cadastrais, módulos contratados e quantidade de máquinas.
 *   - Recalcular o valor total do cliente com base nos módulos selecionados
 *     e no salário mínimo vigente.
 *   - Renovar a licença do cliente automaticamente após a edição.
 * 
 * Diferente do ClienteController (usado pelo representante), este controlador
 * NÃO exige o representante_id nas operações, permitindo acesso irrestrito.
 * 
 * Acesso: Rotas protegidas pelo middleware AuthAdminMiddleware.
 */

require_once __DIR__ . '/../Models/Cliente.php';
require_once __DIR__ . '/../Models/ClienteModulo.php';
require_once __DIR__ . '/../Models/Modulo.php';
require_once __DIR__ . '/../Models/Licenca.php';
use Respect\Validation\Validator as v;

class AdminClienteController {
    
    private Cliente $model;

    public function __construct() {
        $this->model = new Cliente();
    }

    public function editar(int $id): void {
        $cliente = $this->model->buscarPorIdAdmin($id);
        if (!$cliente) {
            echo "Cliente não encontrado.";
            exit;
        }
        
        $moduloModel = new Modulo();
        $modulos = $moduloModel->listarTodos();
        
        $cmModel = new ClienteModulo();
        $modulosCliente = $cmModel->getModulosDoCliente($id);
        $idsModulosCliente = array_column($modulosCliente, 'identificador');
        
        require __DIR__ . '/../Views/admin/clientes/form.php';
    }

    public function salvar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/clientes');
            exit;
        }

        $tipo = $_POST['tipo_pessoa'] ?? null;
        $cpfCnpjRaw = $_POST['cpf_cnpj'] ?? null;
        $nome = $_POST['nome'] ?? null;
        $id = (int)($_POST['id'] ?? 0);

        if ($tipo === null || $cpfCnpjRaw === null || $nome === null || $id === 0) {
            $this->recarregarFormularioComErro($id, "Preencha todos os campos obrigatórios.");
            return;
        }

        $cpfCnpj = preg_replace('/\D/', '', $cpfCnpjRaw);

        try {
            if ($tipo === 'F') {
                v::cpf()->check($cpfCnpj);
            } else {
                v::cnpj()->check($cpfCnpj);
            }
        } catch (\Exception $e) {
            $this->recarregarFormularioComErro($id, "CPF/CNPJ inválido.");
            return;
        }

        $dados = [
            ':tipo_pessoa' => $tipo,
            ':cpf_cnpj' => $cpfCnpj,
            ':ie_rg' => $_POST['ie_rg'] ?? null,
            ':nome' => $nome,
            ':nome_fantasia' => $_POST['nome_fantasia'] ?? null,
            ':data_fundacao' => !empty($_POST['data_fundacao']) ? $_POST['data_fundacao'] : null,
            ':telefone' => $_POST['telefone'] ?? null,
            ':celular' => $_POST['celular'] ?? null,
            ':email' => $_POST['email'] ?? null,
            ':logradouro' => $_POST['logradouro'] ?? null,
            ':numero' => $_POST['numero'] ?? null,
            ':complemento' => $_POST['complemento'] ?? null,
            ':bairro' => $_POST['bairro'] ?? null,
            ':cep' => $_POST['cep'] ?? null,
            ':estado' => $_POST['estado'] ?? null,
            ':municipio' => $_POST['municipio'] ?? null,
            ':observacoes' => $_POST['observacoes'] ?? null,
            ':ativo' => isset($_POST['ativo']) ? 1 : 0
        ];

        $this->model->atualizarAdmin($id, $dados);

        $modulosSelecionados = $_POST['modulos'] ?? [];
        $cmModel = new ClienteModulo();
        $cmModel->sincronizar($id, $modulosSelecionados);

        $moduloModel = new Modulo();
        $valorTotal = $moduloModel->somaValores($modulosSelecionados);
        $this->model->atualizarValorTotal($id, $valorTotal);

        // Captura e atualiza a quantidade de máquinas
        $qtdMaquinas = (int)($_POST['qtd_maquinas'] ?? 1);

        $licencaModel = new Licenca();
        $chave = $licencaModel->gerarChave();
        $licencaModel->criarOuAtualizar($id, $chave, $qtdMaquinas);

        header('Location: /admin/clientes');
        exit;
    }

    private function recarregarFormularioComErro(int $id, string $mensagem): void {
        $erro = $mensagem;
        $cliente = $this->model->buscarPorIdAdmin($id);
        $moduloModel = new Modulo();
        $modulos = $moduloModel->listarTodos();
        $cmModel = new ClienteModulo();
        $modulosCliente = $cmModel->getModulosDoCliente($id);
        $idsModulosCliente = array_column($modulosCliente, 'identificador');
        require __DIR__ . '/../Views/admin/clientes/form.php';
        exit;
    }
}