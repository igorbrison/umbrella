<?php
/**
 * Arquivo: Controllers/ApiController.php
 * Função: Controlador da API REST de licenciamento.
 * 
 * Responsável por:
 *   - Fornecer endpoints para o aplicativo Flutter validar licenças.
 *   - Gerar tokens offline para ativação manual do sistema.
 *   - Validar renovações offline realizadas com token + chave de liberação.
 * 
 * Todos os endpoints recebem e retornam dados no formato JSON.
 * As rotas são públicas e não exigem autenticação por sessão,
 * mas validam as credenciais enviadas em cada requisição.
 */

require_once __DIR__ . '/../Models/Licenca.php';
require_once __DIR__ . '/../Models/ClienteModulo.php';
require_once __DIR__ . '/../Models/TokenRenovacao.php';

class ApiController {

    // ============================================================
    // 1. VALIDAR LICENÇA (ONLINE)
    // ============================================================
    /**
     * Valida a licença de um cliente.
     * 
     * Entrada (JSON):
     *   { "cliente_id": 1, "chave": "abc123..." }
     * 
     * Retorno (Sucesso - 200):
     *   {
     *     "valido": true,
     *     "chave": "nova_chave_gerada",
     *     "expiracao": "2026-09-05",
     *     "modulos": [
     *       { "identificador": "vendas", "nome": "Vendas" },
     *       { "identificador": "estoque", "nome": "Estoque" }
     *     ]
     *   }
     * 
     * Retorno (Erro - 400): { "erro": "Cliente ID e chave são obrigatórios." }
     * Retorno (Erro - 401): { "erro": "Licença inválida ou inativa." }
     * Retorno (Erro - 401): { "erro": "Licença expirada." }
     * 
     * Comportamento:
     *   - Se a licença for válida, RENOVA automaticamente (nova chave e expiração).
     *   - Se a licença estiver expirada, desativa e retorna erro.
     */
    public function validarLicenca(): void {
        // Lê o corpo da requisição JSON
        $input = json_decode(file_get_contents('php://input'), true);
        $clienteId = (int)($input['cliente_id'] ?? 0);
        $chave = $input['chave'] ?? '';

        // Valida campos obrigatórios
        if ($clienteId <= 0 || empty($chave)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Cliente ID e chave são obrigatórios.']);
            exit;
        }

        // Busca a licença do cliente
        $licencaModel = new Licenca();
        $licenca = $licencaModel->buscarPorCliente($clienteId);

        // Verifica se a licença existe, está ativa e a chave confere
        if (!$licenca || $licenca['chave'] !== $chave || !$licenca['ativa']) {
            http_response_code(401);
            echo json_encode(['erro' => 'Licença inválida ou inativa.']);
            exit;
        }

        // Verifica se a data de expiração já passou
        if (strtotime($licenca['data_expiracao']) < time()) {
            $licencaModel->desativar($clienteId);
            http_response_code(401);
            echo json_encode(['erro' => 'Licença expirada.']);
            exit;
        }

        // Licença válida: renova automaticamente (nova chave e expiração)
        $novaChave = $licencaModel->gerarChave();
        $licencaModel->criarOuAtualizar($clienteId, $novaChave);
        $novaExpiracao = (new DateTime())->modify('+30 days')->format('Y-m-d');

        // Retorna os módulos contratados pelo cliente
        $cmModel = new ClienteModulo();
        $modulos = $cmModel->getModulosDoCliente($clienteId);

        echo json_encode([
            'valido' => true,
            'chave' => $novaChave,
            'expiracao' => $novaExpiracao,
            'modulos' => $modulos
        ]);
    }

    // ============================================================
    // 2. GERAR TOKEN OFFLINE
    // ============================================================
    /**
     * Gera um token offline para um cliente.
     * O token é usado no fluxo de renovação manual (sem internet).
     * 
     * Entrada (JSON):
     *   { "cliente_id": 1 }
     * 
     * Retorno (Sucesso - 200):
     *   { "token": "TOKEN_LONGO_EM_BASE64" }
     * 
     * Retorno (Erro - 400): { "erro": "Cliente ID é obrigatório." }
     * Retorno (Erro - 400): { "erro": "Cliente sem licença ativa." }
     * 
     * O cliente envia este token para o administrador, que gera
     * uma chave de liberação a partir dele.
     */
    public function gerarTokenOffline(): void {
        $input = json_decode(file_get_contents('php://input'), true);
        $clienteId = (int)($input['cliente_id'] ?? 0);

        // Valida campo obrigatório
        if ($clienteId <= 0) {
            http_response_code(400);
            echo json_encode(['erro' => 'Cliente ID é obrigatório.']);
            exit;
        }

        // Gera o token offline (pode lançar exceção se não houver licença ativa)
        $tokenModel = new TokenRenovacao();
        try {
            $token = $tokenModel->gerarTokenOffline($clienteId);
            echo json_encode(['token' => $token]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['erro' => $e->getMessage()]);
        }
    }

    // ============================================================
    // 3. VALIDAR RENOVAÇÃO OFFLINE
    // ============================================================
    /**
     * Valida uma renovação offline feita com token + chave de liberação.
     * Fluxo: O cliente envia o token para o admin, que gera uma chave de liberação.
     * O cliente então informa o token e a chave de liberação neste endpoint.
     * 
     * Entrada (JSON):
     *   { "cliente_id": 1, "token": "...", "chave_liberacao": "..." }
     * 
     * Retorno (Sucesso - 200):
     *   {
     *     "valido": true,
     *     "chave": "nova_chave_gerada",
     *     "expiracao": "2026-09-05",
     *     "modulos": [...]
     *   }
     * 
     * Retorno (Erro - 400): { "erro": "Todos os campos são obrigatórios." }
     * Retorno (Erro - 401): { "erro": "Token ou chave de liberação inválidos." }
     */
    public function validarRenovacaoOffline(): void {
        $input = json_decode(file_get_contents('php://input'), true);
        $clienteId = (int)($input['cliente_id'] ?? 0);
        $token = $input['token'] ?? '';
        $chaveLiberacao = $input['chave_liberacao'] ?? '';

        // Valida campos obrigatórios
        if ($clienteId <= 0 || empty($token) || empty($chaveLiberacao)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Todos os campos são obrigatórios.']);
            exit;
        }

        // Verifica se a combinação token + chave de liberação é válida
        $tokenModel = new TokenRenovacao();
        if (!$tokenModel->validarLiberacao($clienteId, $token, $chaveLiberacao)) {
            http_response_code(401);
            echo json_encode(['erro' => 'Token ou chave de liberação inválidos.']);
            exit;
        }

        // Liberação válida: renova a licença (nova chave e expiração)
        $licencaModel = new Licenca();
        $novaChave = $licencaModel->gerarChave();
        $licencaModel->criarOuAtualizar($clienteId, $novaChave);
        $novaExpiracao = (new DateTime())->modify('+30 days')->format('Y-m-d');

        // Retorna os módulos contratados pelo cliente
        $cmModel = new ClienteModulo();
        $modulos = $cmModel->getModulosDoCliente($clienteId);

        echo json_encode([
            'valido' => true,
            'chave' => $novaChave,
            'expiracao' => $novaExpiracao,
            'modulos' => $modulos
        ]);
    }
}