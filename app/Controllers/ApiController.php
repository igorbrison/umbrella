<?php
require_once __DIR__ . '/../Models/Licenca.php';
require_once __DIR__ . '/../Models/ClienteModulo.php';
require_once __DIR__ . '/../Models/TokenRenovacao.php';

class ApiController {
    // Valida licença e retorna módulos + nova data
    public function validarLicenca(): void {
        $input = json_decode(file_get_contents('php://input'), true);
        $clienteId = $input['cliente_id'] ?? 0;
        $chave = $input['chave'] ?? '';

        $licencaModel = new Licenca();
        $licenca = $licencaModel->buscarPorCliente((int)$clienteId);

        if (!$licenca || $licenca['chave'] !== $chave || !$licenca['ativa']) {
            http_response_code(401);
            echo json_encode(['erro' => 'Licença inválida']);
            exit;
        }

        if (strtotime($licenca['data_expiracao']) < time()) {
            $licencaModel->desativar((int)$clienteId);
            http_response_code(401);
            echo json_encode(['erro' => 'Licença expirada']);
            exit;
        }

        // Renova por mais 30 dias
        $novaExpiracao = date('Y-m-d', strtotime('+30 days'));
        $novaChave = $licencaModel->gerarChave();
        $licencaModel->criarOuAtualizar((int)$clienteId, $novaChave, $novaExpiracao);

        $cmModel = new ClienteModulo();
        $modulos = $cmModel->getModulosDoCliente((int)$clienteId);

        echo json_encode([
            'valido' => true,
            'chave' => $novaChave,
            'expiracao' => $novaExpiracao,
            'modulos' => $modulos
        ]);
    }

    // Gera token para renovação offline
    public function gerarTokenOffline(): void {
        $input = json_decode(file_get_contents('php://input'), true);
        $clienteId = $input['cliente_id'] ?? 0;

        $tokenModel = new TokenRenovacao();
        $token = $tokenModel->gerarToken((int)$clienteId);

        echo json_encode(['token' => $token]);
    }

    // Valida renovação offline com chave de liberação
    public function validarRenovacaoOffline(): void {
        $input = json_decode(file_get_contents('php://input'), true);
        $clienteId = $input['cliente_id'] ?? 0;
        $token = $input['token'] ?? '';
        $chaveLiberacao = $input['chave_liberacao'] ?? '';

        $tokenModel = new TokenRenovacao();
        if ($tokenModel->validarLiberacao((int)$clienteId, $token, $chaveLiberacao)) {
            $licencaModel = new Licenca();
            $novaExpiracao = date('Y-m-d', strtotime('+30 days'));
            $novaChave = $licencaModel->gerarChave();
            $licencaModel->criarOuAtualizar((int)$clienteId, $novaChave, $novaExpiracao);

            $cmModel = new ClienteModulo();
            $modulos = $cmModel->getModulosDoCliente((int)$clienteId);

            echo json_encode([
                'valido' => true,
                'chave' => $novaChave,
                'expiracao' => $novaExpiracao,
                'modulos' => $modulos
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['erro' => 'Token ou chave de liberação inválidos']);
        }
    }
}