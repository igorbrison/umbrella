<?php
/**
 * Controlador da API de licenciamento.
 * Fornece endpoints para o aplicativo externo validar licenças,
 * gerar tokens offline e listar módulos disponíveis.
 * 
 * Todas as respostas são em JSON com o formato:
 *   { "status": "success", "data": {...} }
 *   { "status": "error", "message": "..." }
 */

require_once __DIR__ . '/../Models/Licenca.php';
require_once __DIR__ . '/../Models/Modulo.php';
require_once __DIR__ . '/../Models/Representante.php';
require_once __DIR__ . '/../Models/TokenRenovacao.php';

class ApiController
{
    /**
     * Valida uma chave de licença enviada via POST.
     * Espera: { "chave": "..." }
     * Retorna dados do cliente, módulos contratados e status da licença.
     */
    public function validarLicenca(): void
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $chave = $input['chave'] ?? '';

        if (empty($chave)) {
            echo json_encode(['status' => 'error', 'message' => 'Chave não informada.']);
            exit;
        }

        $licencaModel = new Licenca();
        $dados = $licencaModel->buscarPorChave($chave);

        if (!$dados) {
            echo json_encode(['status' => 'error', 'message' => 'Chave inválida.']);
            exit;
        }

        // Verifica expiração
        $hoje = new DateTime();
        $expiracao = new DateTime($dados['data_expiracao']);
        if ($expiracao < $hoje) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Licença expirada.',
                'expirada_em' => $dados['data_expiracao']
            ]);
            exit;
        }

        // Busca módulos contratados
        $modulos = $licencaModel->getModulosCliente($dados['cliente_id']);

        echo json_encode([
            'status' => 'success',
            'data' => [
                'cliente_id'      => $dados['cliente_id'],
                'cliente_nome'    => $dados['cliente_nome'],
                'chave'           => $chave,
                'data_expiracao'  => $dados['data_expiracao'],
                'qtd_maquinas'    => $dados['qtd_maquinas'],
                'modulos'         => array_column($modulos, 'identificador'),
                'ativa'           => (bool)$dados['ativa'],
            ]
        ]);
    }

    /**
     * Gera um token offline para ativação manual.
     * Espera: { "chave": "...", "cliente_id": ... }
     * Retorna o token gerado.
     */
    public function gerarTokenOffline(): void
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $chave = $input['chave'] ?? '';
        $clienteId = (int)($input['cliente_id'] ?? 0);

        if (empty($chave) || $clienteId <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Parâmetros inválidos.']);
            exit;
        }

        // Verifica se a chave pertence ao cliente
        $licencaModel = new Licenca();
        $dados = $licencaModel->buscarPorChave($chave);
        if (!$dados || $dados['cliente_id'] !== $clienteId) {
            echo json_encode(['status' => 'error', 'message' => 'Chave ou cliente inválido.']);
            exit;
        }

        $tokenModel = new TokenRenovacao();
        try {
            $token = $tokenModel->gerarTokenOffline($clienteId);
            echo json_encode(['status' => 'success', 'data' => ['token' => $token]]);
        } catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Valida um token de renovação offline.
     * Espera: { "token": "...", "cliente_id": ... }
     * Retorna a nova chave se o token for válido.
     */
    public function validarRenovacaoOffline(): void
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $token = $input['token'] ?? '';
        $clienteId = (int)($input['cliente_id'] ?? 0);

        if (empty($token) || $clienteId <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Parâmetros inválidos.']);
            exit;
        }

        $tokenModel = new TokenRenovacao();
        $valido = $tokenModel->validarToken($clienteId, $token);

        if (!$valido) {
            echo json_encode(['status' => 'error', 'message' => 'Token inválido ou expirado.']);
            exit;
        }

        // Gera nova chave
        $licencaModel = new Licenca();
        $chave = $licencaModel->gerarChave();
        $licencaModel->criarOuAtualizar($clienteId, $chave);

        // Remove o token usado
        $tokenModel->removerToken($clienteId, $token);

        echo json_encode([
            'status' => 'success',
            'data' => ['nova_chave' => $chave]
        ]);
    }

    /**
     * Lista todos os módulos disponíveis (ativos).
     */
    public function listarModulos(): void
    {
        header('Content-Type: application/json');

        $moduloModel = new Modulo();
        $modulos = $moduloModel->listarTodosAtivos();

        echo json_encode([
            'status' => 'success',
            'data' => $modulos
        ]);
    }

    /**
     * Autenticação do representante via API.
     * Espera: { "email": "...", "senha": "..." }
     * Retorna os dados do representante se as credenciais forem válidas.
     */
    public function login(): void
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $email = $input['email'] ?? '';
        $senha = $input['senha'] ?? '';

        if (empty($email) || empty($senha)) {
            echo json_encode(['status' => 'error', 'message' => 'E-mail e senha são obrigatórios.']);
            exit;
        }

        $repModel = new Representante();
        $representante = $repModel->autenticar($email, $senha);

        if (!$representante) {
            echo json_encode(['status' => 'error', 'message' => 'Credenciais inválidas.']);
            exit;
        }

        echo json_encode([
            'status' => 'success',
            'data' => $representante
        ]);
    }
}