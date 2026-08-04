<?php
require_once __DIR__ . '/../Models/Licenca.php';
require_once __DIR__ . '/../Models/ClienteModulo.php';
require_once __DIR__ . '/../Models/TokenRenovacao.php';

class ApiController {

    /**
     * Valida a licença do cliente.
     * Entrada (JSON): { "cliente_id": 1, "chave": "..." }
     * Retorno: { "valido": true, "chave": "...", "expiracao": "YYYY-MM-DD", "modulos": [...] }
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

        // Renova automaticamente por mais 30 dias e gera nova chave
        $novaChave = $licencaModel->gerarChave();
        $licencaModel->criarOuAtualizar($clienteId, $novaChave);
        $novaExpiracao = (new DateTime())->modify('+30 days')->format('Y-m-d');

        $cmModel = new ClienteModulo();
        $modulos = $cmModel->getModulosDoCliente($clienteId);

        echo json_encode([
            'valido' => true,
            'chave' => $novaChave,
            'expiracao' => $novaExpiracao,
            'modulos' => $modulos
        ]);
    }

    /**
     * Gera um token offline que o cliente informa ao admin.
     * Entrada (JSON): { "cliente_id": 1 }
     * Retorno: { "token": "..." }
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

    /**
     * Valida uma renovação offline feita com token + chave de liberação.
     * Entrada (JSON): { "cliente_id": 1, "token": "...", "chave_liberacao": "..." }
     * Retorno: { "valido": true, "chave": "...", "expiracao": "YYYY-MM-DD", "modulos": [...] }
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
        $licencaModel->criarOuAtualizar($clienteId, $novaChave);
        $novaExpiracao = (new DateTime())->modify('+30 days')->format('Y-m-d');

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