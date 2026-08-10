<?php
/**
 * Controlador da entidade "Representante".
 */
require_once __DIR__ . '/../Models/Representante.php';
use Respect\Validation\Validator as v;

class RepresentanteController {

    private Representante $model;

    public function __construct() {
        $this->model = new Representante();
    }

    public function index(): void {
        $ordem = $_GET['ordem'] ?? 'id';
        $direcao = $_GET['direcao'] ?? 'asc';
        $pagina = (int)($_GET['pagina'] ?? 1);

        $resultado = $this->model->listarPaginado($ordem, $direcao, $pagina, 10);
        $representantes = $resultado['dados'];
        $ordenacaoAtual = ['coluna' => $ordem, 'direcao' => $direcao];
        $paginacao = [
            'pagina_atual'   => $resultado['pagina_atual'],
            'total_paginas'  => $resultado['total_paginas'],
        ];

        require __DIR__ . '/../Views/representantes/listar.php';
    }

    public function criar(): void {
        require __DIR__ . '/../Views/representantes/form.php';
    }

    public function salvar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/representantes');
            exit;
        }
        if (!empty($_POST['senha']) && $_POST['senha'] !== ($_POST['confirmar_senha'] ?? '')) {
            echo "Erro: as senhas não conferem. <a href='javascript:history.back()'>Voltar</a>";
            exit;
        }
        $cnpj = preg_replace('/\D/', '', $_POST['cnpj']);
        try {
            v::cnpj()->check($cnpj);
        } catch (\Exception $e) {
            echo "CNPJ inválido. <a href='javascript:history.back()'>Voltar</a>";
            exit;
        }

        $dados = [
            ':cnpj' => $cnpj,
            ':inscricao_estadual' => $_POST['inscricao_estadual'] ?? null,
            ':nome_razao' => $_POST['nome_razao'],
            ':nome_fantasia' => $_POST['nome_fantasia'] ?? null,
            ':nome_exibicao' => $_POST['nome_exibicao'] ?? null,
            ':cnae' => $_POST['cnae'] ?? null,
            ':crt' => $_POST['crt'] ?? null,
            ':data_fundacao' => !empty($_POST['data_fundacao']) ? $_POST['data_fundacao'] : null,
            ':comissao_percentual' => $_POST['comissao_percentual'] ?? null,
            ':logradouro' => $_POST['logradouro'] ?? null,
            ':numero' => $_POST['numero'] ?? null,
            ':complemento' => $_POST['complemento'] ?? null,
            ':bairro' => $_POST['bairro'] ?? null,
            ':cep' => $_POST['cep'] ?? null,
            ':estado' => $_POST['estado'] ?? null,
            ':municipio' => $_POST['municipio'] ?? null,
            ':telefone' => $_POST['telefone'] ?? null,
            ':celular' => $_POST['celular'] ?? null,
            ':email' => $_POST['email'] ?? null,
            ':observacoes' => $_POST['observacoes'] ?? null,
            ':ativo' => 1,
            ':senha' => $_POST['senha'] ?? ''
        ];

        if (!empty($_POST['id'])) {
            $id = (int)$_POST['id'];
            if (empty($_POST['senha'])) unset($dados[':senha']);
            $this->model->atualizar($id, $dados);
        } else {
            $this->model->inserir($dados);
        }
        header('Location: /admin/representantes');
        exit;
    }

    public function editar(int $id): void {
        $representante = $this->model->buscarPorId($id);
        if (!$representante) {
            echo "Representante não encontrado.";
            exit;
        }
        require __DIR__ . '/../Views/representantes/form.php';
    }

    public function status(int $id): void {
        $r = $this->model->buscarPorId($id);
        if ($r) $this->model->alterarStatus($id, $r['ativo'] ? 0 : 1);
        header('Location: /admin/representantes');
        exit;
    }

    public function excluir(int $id): void {
        $this->model->excluir($id);
        header('Location: /admin/representantes');
        exit;
    }

    public function comissoes(): void {
        $mesReferencia = $_GET['mes'] ?? date('Y-m', strtotime('first day of last month'));
        $filtro = $_GET['status'] ?? 'todos';
        $pagina = (int)($_GET['pagina'] ?? 1);
        $limite = 10;

        $todosRepresentantes = $this->model->comissaoPorRepresentante($mesReferencia);

        if ($filtro === 'pendentes') {
            $todosRepresentantes = array_filter($todosRepresentantes, function($r) {
                return $r['comissao_devida'] > 0 && ($r['comissao_devida'] - $r['comissao_paga']) > 0;
            });
        } elseif ($filtro === 'pagos') {
            $todosRepresentantes = array_filter($todosRepresentantes, function($r) {
                return $r['comissao_devida'] > 0 && ($r['comissao_devida'] - $r['comissao_paga']) <= 0;
            });
        }

        $total = count($todosRepresentantes);
        $totalPaginas = ceil($total / $limite);
        $offset = ($pagina - 1) * $limite;
        $representantes = array_slice($todosRepresentantes, $offset, $limite);

        $paginacao = ['pagina_atual' => $pagina, 'total_paginas' => $totalPaginas];

        require __DIR__ . '/../Views/representantes/comissoes.php';
    }

    public function pagarComissao(): void {
        $representanteId = (int)($_POST['representante_id'] ?? 0);
        $valor = (float)($_POST['valor'] ?? 0);
        $mesReferencia = $_POST['mes_referencia'] ?? date('Y-m', strtotime('first day of last month'));
        $observacao = $_POST['observacao'] ?? '';

        if ($representanteId <= 0 || $valor <= 0) {
            echo "Dados inválidos.";
            exit;
        }
        $this->model->registrarPagamentoComissao($representanteId, $valor, $mesReferencia, $observacao);
        header('Location: /admin/representantes/comissoes?mes=' . $mesReferencia);
        exit;
    }

    /**
     * Relatório detalhado de comissão de um representante (HTML imprimível).
     */
    public function relatorioComissao(int $representanteId): void {
        $mesReferencia = $_GET['mes'] ?? date('Y-m', strtotime('first day of last month'));

        $rep = $this->model->buscarPorId($representanteId);
        if (!$rep) {
            echo "Representante não encontrado.";
            exit;
        }

        $clientes = $this->model->buscarClientesComissao($representanteId, $mesReferencia);
        $totalComissao = $this->model->getValorComissao($representanteId, $mesReferencia);

        // Total já pago ao representante (consulta direta via PDO público)
        $stmt = $this->model->getPdo()->prepare("
            SELECT COALESCE(SUM(valor), 0) FROM comissao_pagamentos
            WHERE representante_id = :rid AND mes_referencia = :mes
        ");
        $stmt->execute([':rid' => $representanteId, ':mes' => $mesReferencia]);
        $totalPago = (float)$stmt->fetchColumn();

        // Buscar os pagamentos efetuados (para possível edição de observação)
        $pagamentosStmt = $this->model->getPdo()->prepare("
            SELECT id, valor, observacao FROM comissao_pagamentos
            WHERE representante_id = :rid AND mes_referencia = :mes
            ORDER BY data_pagamento DESC
        ");
        $pagamentosStmt->execute([':rid' => $representanteId, ':mes' => $mesReferencia]);
        $pagamentos = $pagamentosStmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../Views/representantes/relatorio_comissao.php';
    }

    /**
     * Atualiza a observação de um pagamento de comissão.
     */
    public function editarObservacao(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        $observacao = $_POST['observacao'] ?? '';

        if ($id <= 0) {
            echo json_encode(['ok' => false]);
            exit;
        }

        $this->model->atualizarObservacaoComissao($id, $observacao);
        echo json_encode(['ok' => true]);
    }
}