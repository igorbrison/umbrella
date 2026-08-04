<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Licenca.php';
require_once __DIR__ . '/ClienteModulo.php';

class TokenRenovacao {
    private \PDO $pdo;
    const CHAVE_SECRETA = 'UMBRELLA_SECRET_2024';

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

    // Busca um token não usado
    public function buscarPorToken(string $token): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM tokens_renovacao WHERE token = :token");
        $stmt->execute([':token' => $token]);
        return $stmt->fetch() ?: null;
    }

    // Gera a chave de liberação e marca o token como usado
    public function gerarChaveLiberacao(string $token): ?string {
        $registro = $this->buscarPorToken($token);
        if (!$registro) return null;

        $chave = bin2hex(random_bytes(32));
        $stmt = $this->pdo->prepare(
            "UPDATE tokens_renovacao SET chave_liberacao = :chave, usado = 1 WHERE token = :token"
        );
        $stmt->execute([':chave' => $chave, ':token' => $token]);
        return $chave;
    }

    // Valida se o token e a chave de liberação são válidos para o cliente
    public function validarLiberacao(int $clienteId, string $token, string $chaveLiberacao): bool {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM tokens_renovacao 
             WHERE cliente_id = :cid 
               AND token = :token 
               AND chave_liberacao = :chave 
               AND usado = 1"
        );
        $stmt->execute([
            ':cid' => $clienteId, 
            ':token' => $token, 
            ':chave' => $chaveLiberacao
        ]);
        return (bool) $stmt->fetch();
    }
}