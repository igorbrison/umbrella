<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Licenca.php';
require_once __DIR__ . '/ClienteModulo.php';

class TokenRenovacao {
    private \PDO $pdo;
    const CHAVE_SECRETA = 'UMBRELLA_SECRET_2024'; // mesma chave no app Flutter

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    // Gera um token offline que contém os módulos e data de expiração, assinado com HMAC
    public function gerarTokenOffline(int $clienteId): string {
        $licencaModel = new Licenca();
        $licenca = $licencaModel->buscarPorCliente($clienteId);
        if (!$licenca || !$licenca['ativa']) {
            throw new \Exception('Cliente sem licença ativa.');
        }

        $modulos = (new ClienteModulo())->getModulosDoCliente($clienteId);
        $payload = [
            'cliente_id' => $clienteId,
            'expiracao' => $licenca['data_expiracao'],
            'modulos' => array_column($modulos, 'identificador')
        ];
        $payloadJson = json_encode($payload);
        $assinatura = hash_hmac('sha256', $payloadJson, self::CHAVE_SECRETA);
        $token = base64_encode($payloadJson . '::' . $assinatura);

        // Registra o token gerado (histórico)
        $stmt = $this->pdo->prepare("INSERT INTO tokens_renovacao (cliente_id, token) VALUES (:cid, :token)");
        $stmt->execute([':cid' => $clienteId, ':token' => $token]);

        return $token;
    }
}