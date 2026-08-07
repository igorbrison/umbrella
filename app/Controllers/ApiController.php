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
     *     "modulos": [...],
     *     "qtd_maquinas": 3
     *   }
     */
    public function validarLicenca(): void {
        $input = json_decode(file_get_contents('php://input'), true);
        $clienteId = (int)($input['cliente_id'] ?? 0);
        $chave = $input['chave'] ?? '';

        if ($clienteId <= 0 || empty($chave)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Cliente ID e chave são obrigatórios.']);
            exit;
        }

        $licencaModel = new Licenca();
        $licenca = $licencaModel->buscarPorCliente($clienteId);

        if (!$licenca || $licenca['chave'] !== $chave || !$licenca['ativa']) {
            http_response_code(401);
            echo json_encode(['erro' => 'Licença inválida ou inativa.']);
            exit;
        }

        if (strtotime($licenca['data_expiracao']) < time()) {
            $licencaModel->desativar($clienteId);
            http_response_code(401);
            echo json_encode(['erro' => 'Licença expirada.']);
            exit;
        }

        // Licença válida: renova automaticamente
        $novaChave = $licencaModel->gerarChave();
        $qtdMaquinas = (int)($licenca['qtd_maquinas'] ?? 1);
        $licencaModel->criarOuAtualizar($clienteId, $novaChave, $qtdMaquinas);
        $novaExpiracao = (new DateTime())->modify('+30 days')->format('Y-m-d');

        $cmModel = new ClienteModulo();
        $modulos = $cmModel->getModulosDoCliente($clienteId);

        echo json_encode([
            'valido' => true,
            'chave' => $novaChave,
            'expiracao' => $novaExpiracao,
            'modulos' => $modulos,
            'qtd_maquinas' => $qtdMaquinas
        ]);
    }

    // ============================================================
    // 2. GERAR TOKEN OFFLINE
    // ============================================================
    /**
     * Gera um token offline para um cliente.
     */
    public function gerarTokenOffline(): void {
        $input = json_decode(file_get_contents('php://input'), true);
        $clienteId = (int)($input['cliente_id'] ?? 0);

        if ($clienteId <= 0) {
            http_response_code(400);
            echo json_encode(['erro' => 'Cliente ID é obrigatório.']);
            exit;
        }

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
     */
    public function validarRenovacaoOffline(): void {
        $input = json_decode(file_get_contents('php://input'), true);
        $clienteId = (int)($input['cliente_id'] ?? 0);
        $token = $input['token'] ?? '';
        $chaveLiberacao = $input['chave_liberacao'] ?? '';

        if ($clienteId <= 0 || empty($token) || empty($chaveLiberacao)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Todos os campos são obrigatórios.']);
            exit;
        }

        $tokenModel = new TokenRenovacao();
        if (!$tokenModel->validarLiberacao($clienteId, $token, $chaveLiberacao)) {
            http_response_code(401);
            echo json_encode(['erro' => 'Token ou chave de liberação inválidos.']);
            exit;
        }

        // Liberação válida: renova licença
        $licencaModel = new Licenca();
        $novaChave = $licencaModel->gerarChave();
        $licencaAtual = $licencaModel->buscarPorCliente($clienteId);
        $qtdMaquinas = (int)($licencaAtual['qtd_maquinas'] ?? 1);
        $licencaModel->criarOuAtualizar($clienteId, $novaChave, $qtdMaquinas);
        $novaExpiracao = (new DateTime())->modify('+30 days')->format('Y-m-d');

        $cmModel = new ClienteModulo();
        $modulos = $cmModel->getModulosDoCliente($clienteId);

        echo json_encode([
            'valido' => true,
            'chave' => $novaChave,
            'expiracao' => $novaExpiracao,
            'modulos' => $modulos,
            'qtd_maquinas' => $qtdMaquinas
        ]);
    }
}