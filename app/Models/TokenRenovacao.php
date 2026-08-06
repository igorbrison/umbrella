<?php
/**
 * Arquivo: Models/TokenRenovacao.php
 * Função: Model responsável pela geração e validação de tokens de renovação offline.
 * 
 * Responsabilidades:
 *   - Gerar tokens offline contendo informações de licença e módulos, assinados com HMAC.
 *   - Registrar os tokens gerados no banco de dados para histórico.
 *   - Gerar chaves de liberação associadas a tokens (para renovação manual pelo admin).
 *   - Validar se uma chave de liberação é válida para um determinado cliente e token.
 * 
 * Fluxo offline:
 *   1. O representante (ou admin) gera um token offline para um cliente.
 *   2. O token é enviado ao cliente, que o insere no aplicativo Flutter.
 *   3. O app decodifica o token, verifica a assinatura HMAC e extrai os módulos e data de expiração.
 *   4. Para renovação manual, o admin gera uma chave de liberação a partir do token,
 *      e o app valida essa chave via API.
 * 
 * Conexão: Utiliza o Singleton Database para obter uma instância PDO única.
 * Dependências: Models Licenca e ClienteModulo para obter dados da licença e módulos.
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Licenca.php';
require_once __DIR__ . '/ClienteModulo.php';

class TokenRenovacao {
    
    /**
     * @var \PDO $pdo
     * Instância do PDO para executar consultas SQL.
     */
    private \PDO $pdo;
    
    /**
     * @var string CHAVE_SECRETA
     * Chave secreta usada para assinar tokens offline (HMAC-SHA256).
     * Deve ser a mesma no aplicativo Flutter para validação.
     */
    const CHAVE_SECRETA = 'UMBRELLA_SECRET_2024';

    /**
     * Construtor da classe.
     * Obtém a instância única do banco de dados via Database::getInstance().
     */
    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    /**
     * Gera um token offline para um cliente.
     * O token contém:
     *   - ID do cliente
     *   - Data de expiração da licença
     *   - Lista de identificadores dos módulos contratados
     * Esses dados são codificados em JSON, assinados com HMAC-SHA256 e codificados em base64.
     * O token também é registrado no banco de dados (tabela tokens_renovacao) para histórico.
     * 
     * @param int $clienteId ID do cliente.
     * @return string Token offline gerado.
     * @throws \Exception Se o cliente não tiver licença ativa.
     */
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

        // Registra o token gerado no banco (histórico)
        $stmt = $this->pdo->prepare("INSERT INTO tokens_renovacao (cliente_id, token) VALUES (:cid, :token)");
        $stmt->execute([':cid' => $clienteId, ':token' => $token]);

        return $token;
    }

    /**
     * Busca um token na tabela tokens_renovacao.
     * 
     * @param string $token Token a ser pesquisado.
     * @return array|null Dados do registro ou null se não encontrado.
     */
    public function buscarPorToken(string $token): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM tokens_renovacao WHERE token = :token");
        $stmt->execute([':token' => $token]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Gera uma chave de liberação para um token e marca o token como usado.
     * Essa chave é fornecida pelo administrador ao cliente para renovação offline manual.
     * 
     * @param string $token Token original gerado.
     * @return string|null Chave de liberação gerada, ou null se o token não for encontrado.
     */
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

    /**
     * Valida se uma chave de liberação é válida para um determinado cliente e token.
     * Verifica se existe um registro em tokens_renovacao com:
     *   - cliente_id = fornecido
     *   - token = fornecido
     *   - chave_liberacao = fornecida
     *   - usado = 1 (já foi gerado pelo admin)
     * 
     * @param int $clienteId ID do cliente.
     * @param string $token Token original.
     * @param string $chaveLiberacao Chave de liberação fornecida.
     * @return bool True se a combinação for válida, false caso contrário.
     */
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