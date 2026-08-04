<?php

/**
 * Arquivo: Models/Representante.php
 * Função: MODEL da entidade "Representante".
 * 
 * Responsabilidades:
 *   - Gerenciar todas as operações de banco de dados relacionadas aos representantes.
 *   - Aplicar regras de negócio antes de persistir os dados (ex: hash de senha).
 *   - Garantir a segurança contra SQL Injection (uso de prepared statements).
 *   - Oferecer métodos específicos para ordenação, busca, inserção, atualização, exclusão e alteração de status.
 * 
 * Padrão: Active Record simplificado (cada instância representa uma conexão com a tabela).
 * Conexão: Utiliza o Singleton Database para obter uma instância PDO única.
 */

// Inclui a classe Database para obter a conexão PDO.
require_once __DIR__ . '/Database.php';

class Representante {
    
    /**
     * @var \PDO $pdo 
     * Instância do PDO (PHP Data Objects) para executar consultas SQL.
     * Privada para garantir que apenas esta classe manipule a conexão diretamente.
     */
    private \PDO $pdo;

    /**
     * Construtor da classe.
     * Obtém a instância única do banco de dados via método estático Database::getInstance().
     * Essa abordagem garante que haja apenas uma conexão ativa durante toda a requisição.
     */
    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    /**
     * Método: listarComOrdenacao()
     * 
     * Retorna todos os registros da tabela 'representantes', ordenados conforme os parâmetros.
     * 
     * @param string $coluna  Nome da coluna pela qual ordenar (padrão: 'id').
     * @param string $direcao Direção da ordenação: 'asc' ou 'desc' (padrão: 'asc').
     * @return array Lista de representantes (cada elemento é um array associativo).
     * 
     * SEGURANÇA CONTRA SQL INJECTION:
     *   - Utiliza uma LISTA BRANCA (whitelist) de colunas permitidas para ordenação.
     *   - Qualquer coluna não listada é substituída por 'id' (valor seguro).
     *   - A direção é normalizada para 'ASC' ou 'DESC' (maiúsculas), garantindo que apenas esses dois valores sejam usados.
     *   - Isso é essencial porque os nomes das colunas e a direção não podem ser parametrizados com prepared statements.
     */
    public function listarComOrdenacao(string $coluna = 'id', string $direcao = 'asc'): array {
        // Definição das colunas que podem ser usadas na ordenação.
        // Isso evita que um usuário mal-intencionado passe, por exemplo, 'id; DROP TABLE ...' via GET.
        $colunasPermitidas = [
            'id', 'nome_razao', 'nome_fantasia', 'cnpj', 'email',
            'comissao_percentual', 'ativo'
        ];
        
        // Se a coluna solicitada não estiver na lista, usa 'id' como fallback.
        if (!in_array($coluna, $colunasPermitidas)) {
            $coluna = 'id';
        }
        
        // Normaliza a direção: só permite 'ASC' ou 'DESC'. Qualquer outra coisa vira 'ASC'.
        $direcao = strtolower($direcao) === 'desc' ? 'DESC' : 'ASC';

        // Monta a consulta SQL com a coluna e direção já validadas.
        // Como a interpolação é segura (valores controlados), podemos fazer isso diretamente.
        $sql = "SELECT * FROM representantes ORDER BY $coluna $direcao";
        
        // Executa a consulta diretamente (sem prepared statement, pois não há parâmetros externos).
        $stmt = $this->pdo->query($sql);
        
        // Retorna todos os registros como um array associativo.
        return $stmt->fetchAll();
    }

    /**
     * Método: listarTodos()
     * 
     * Mantido para compatibilidade com código antigo.
     * Simplesmente chama o novo método listarComOrdenacao com os valores padrão.
     * 
     * @return array Lista de representantes ordenados por ID ascendente.
     */
    public function listarTodos(): array {
        return $this->listarComOrdenacao('id', 'asc');
    }

    /**
     * Método: buscarPorId()
     * 
     * Busca um representante específico pelo seu ID.
     * 
     * @param int $id ID do representante.
     * @return array|null Retorna os dados do representante ou null se não encontrado.
     * 
     * SEGURANÇA: Utiliza prepared statement para evitar SQL Injection.
     */
    public function buscarPorId(int $id): ?array {
        // Prepara a consulta com um placeholder :id.
        $stmt = $this->pdo->prepare("SELECT * FROM representantes WHERE id = :id");
        // Executa vinculando o valor do ID (inteiro) ao placeholder.
        $stmt->execute([':id' => $id]);
        // Recupera o primeiro registro encontrado. Se não houver, retorna null.
        return $stmt->fetch() ?: null;
    }

    /**
     * Método: buscarPorEmail()
     * 
     * Busca um representante pelo endereço de e-mail.
     * Útil para validações de unicidade ou recuperação de senha.
     * 
     * @param string $email E-mail a ser pesquisado.
     * @return array|null Retorna os dados do representante ou null se não encontrado.
     * 
     * SEGURANÇA: Prepared statement com binding de string.
     */
    public function buscarPorEmail(string $email): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM representantes WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Método: inserir()
     * 
     * Insere um novo representante no banco de dados.
     * 
     * @param array $dados Array associativo com as chaves correspondentes às colunas da tabela,
     *                     incluindo ':senha' (já validada no controller).
     * @return bool True se a inserção for bem-sucedida, false caso contrário.
     * 
     * DETALHES:
     *   - A senha é aplicada com password_hash() usando o algoritmo PASSWORD_DEFAULT (atualmente bcrypt).
     *   - Isso garante que a senha nunca seja armazenada em texto puro.
     *   - Utiliza prepared statement com binding de todos os parâmetros.
     */
    public function inserir(array $dados): bool {
        // Se a senha foi fornecida e não está vazia, aplica o hash.
        if (!empty($dados[':senha'])) {
            $dados[':senha'] = password_hash($dados[':senha'], PASSWORD_DEFAULT);
        }

        // Monta a instrução INSERT com todos os campos.
        $sql = "INSERT INTO representantes 
        (cnpj, inscricao_estadual, nome_razao, nome_fantasia, nome_exibicao, cnae, crt,
         data_fundacao, comissao_percentual, logradouro, numero, complemento,
         bairro, cep, estado, municipio, telefone, celular, email, observacoes, ativo, senha)
        VALUES 
        (:cnpj, :inscricao_estadual, :nome_razao, :nome_fantasia, :nome_exibicao, :cnae, :crt,
         :data_fundacao, :comissao_percentual, :logradouro, :numero, :complemento,
         :bairro, :cep, :estado, :municipio, :telefone, :celular, :email, :observacoes, :ativo, :senha)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($dados);
    }

    /**
     * Método: atualizar()
     * 
     * Atualiza os dados de um representante existente.
     * 
     * @param int   $id    ID do representante a ser atualizado.
     * @param array $dados Array associativo com as chaves correspondentes às colunas.
     * @return bool True se a atualização for bem-sucedida, false caso contrário.
     * 
     * LÓGICA DE SEGURANÇA PARA SENHA:
     *   - Se a chave ':senha' existir e não estiver vazia, aplica o hash e inclui a coluna 'senha' no UPDATE.
     *   - Caso contrário, remove a chave ':senha' do array e NÃO atualiza a coluna 'senha' no banco.
     *   - Isso permite que o usuário deixe a senha em branco na edição para mantê-la inalterada.
     */
    public function atualizar(int $id, array $dados): bool {
        // Verifica se a senha foi fornecida e não é vazia.
        if (!empty($dados[':senha'])) {
            // Aplica o hash na senha.
            $dados[':senha'] = password_hash($dados[':senha'], PASSWORD_DEFAULT);
            // SQL com a coluna 'senha' incluída.
            $sql = "UPDATE representantes SET 
                    cnpj=:cnpj, inscricao_estadual=:inscricao_estadual, nome_razao=:nome_razao, nome_fantasia=:nome_fantasia,
                    nome_exibicao=:nome_exibicao,
                    cnae=:cnae, crt=:crt, data_fundacao=:data_fundacao,
                    comissao_percentual=:comissao_percentual, logradouro=:logradouro, numero=:numero,
                    complemento=:complemento, bairro=:bairro, cep=:cep, estado=:estado,
                    municipio=:municipio, telefone=:telefone, celular=:celular, email=:email,
                    observacoes=:observacoes, ativo=:ativo, senha=:senha
                    WHERE id=:id";
        } else {
            // Senha não foi fornecida: remove a chave do array para não atrapalhar o binding.
            unset($dados[':senha']);
            // SQL sem a coluna 'senha'.
            $sql = "UPDATE representantes SET 
                    cnpj=:cnpj, inscricao_estadual=:inscricao_estadual, nome_razao=:nome_razao, nome_fantasia=:nome_fantasia,
                    nome_exibicao=:nome_exibicao,
                    cnae=:cnae, crt=:crt, data_fundacao=:data_fundacao,
                    comissao_percentual=:comissao_percentual, logradouro=:logradouro, numero=:numero,
                    complemento=:complemento, bairro=:bairro, cep=:cep, estado=:estado,
                    municipio=:municipio, telefone=:telefone, celular=:celular, email=:email,
                    observacoes=:observacoes, ativo=:ativo
                    WHERE id=:id";
        }
        // Adiciona o ID ao array de parâmetros para o binding.
        $dados[':id'] = $id;
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($dados);
    }

    /**
     * Método: alterarStatus()
     * 
     * Altera apenas o campo 'ativo' de um representante (toggle).
     * 
     * @param int $id    ID do representante.
     * @param int $ativo Valor booleano (0 ou 1) para o novo status.
     * @return bool True se a atualização for bem-sucedida, false caso contrário.
     * 
     * USO: Geralmente chamado pelo método 'status' do controller.
     */
    public function alterarStatus(int $id, int $ativo): bool {
        $stmt = $this->pdo->prepare("UPDATE representantes SET ativo = :ativo WHERE id = :id");
        return $stmt->execute([':ativo' => $ativo, ':id' => $id]);
    }

    /**
     * Método: excluir()
     * 
     * Remove permanentemente um representante do banco de dados.
     * 
     * @param int $id ID do representante a ser excluído.
     * @return bool True se a exclusão for bem-sucedida, false caso contrário.
     * 
     * ATENÇÃO: Esta operação é irreversível. Em sistemas críticos, considere
     * usar 'soft delete' (apenas marcar como inativo) em vez de exclusão física.
     */
    public function excluir(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM representantes WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Método: buscarPorCnpj()
     * 
     * Busca um representante pelo CNPJ (usado no login).
     * 
     * @param string $cnpj CNPJ apenas com números.
     * @return array|null Dados do representante ou null.
     */
    public function buscarPorCnpj(string $cnpj): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM representantes WHERE cnpj = :cnpj");
        $stmt->execute([':cnpj' => $cnpj]);
        return $stmt->fetch() ?: null;
    }
}