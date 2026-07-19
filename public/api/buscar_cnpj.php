<?php

/**
 * Arquivo: consulta_cnpj.php (ou similar - ex: api/receita.php)
 * Função: ENDPOINT DE API (Proxy) para consulta de CNPJ.
 * 
 * Este script atua como um "intermediário" entre o frontend (JavaScript) 
 * e a API pública da ReceitaWS (https://www.receitaws.com.br).
 * 
 * Motivos para usar um proxy no backend:
 *   1. Evitar problemas de CORS (Cross-Origin Resource Sharing) que bloqueiam 
 *      requisições diretas do navegador para APIs externas.
 *   2. Centralizar a lógica de validação e formatação do CNPJ.
 *   3. Possibilitar a adição de cache ou logs futuramente.
 *   4. Proteger as credenciais (se um dia a API exigir chave, fica aqui no servidor).
 * 
 * Formato de retorno: Sempre JSON (seja sucesso ou erro).
 */

// 1. DEFINIÇÃO DO HEADER DE RESPOSTA
// --------------------------------------------------------------
// Informa ao navegador que o conteúdo retornado será um JSON codificado em UTF-8.
// Isso garante que caracteres especiais (ç, ã, etc.) sejam exibidos corretamente.
header('Content-Type: application/json; charset=utf-8');

// 2. VALIDAÇÃO DO PARÂMETRO OBRIGATÓRIO
// --------------------------------------------------------------
// Verifica se a variável 'cnpj' foi enviada via URL (método GET).
// Exemplo de requisição esperada: GET /consulta_cnpj.php?cnpj=12345678000199
if (!isset($_GET['cnpj'])) {
    // Retorna um JSON com status de erro e interrompe a execução.
    echo json_encode(['status' => 'ERROR', 'message' => 'CNPJ não informado']);
    exit;
}

// 3. LIMPEZA E FORMATAÇÃO DO CNPJ
// --------------------------------------------------------------
// Remove todos os caracteres que NÃO são dígitos (0-9).
// Isso trata entradas como "12.345.678/0001-99" ou "12 345 678 0001 99",
// transformando-as em uma string limpa de 14 números.
$cnpj = preg_replace('/\D/', '', $_GET['cnpj']);

// 4. VALIDAÇÃO DO TAMANHO
// --------------------------------------------------------------
// O CNPJ válido no Brasil tem exatamente 14 dígitos numéricos.
// A validação matemática (dígitos verificadores) será feita pela API externa,
// mas já verificamos o comprimento para evitar chamadas desnecessárias.
if (strlen($cnpj) !== 14) {
    echo json_encode(['status' => 'ERROR', 'message' => 'CNPJ inválido']);
    exit;
}

// 5. CONFIGURAÇÃO DA REQUISIÇÃO HTTP PARA A API EXTERNA
// --------------------------------------------------------------
// Monta a URL da API pública da ReceitaWS.
// Exemplo: https://www.receitaws.com.br/v1/cnpj/12345678000199
$url = 'https://www.receitaws.com.br/v1/cnpj/' . $cnpj;

// Configura as opções da requisição HTTP usando um array.
// Este array será convertido em um "contexto" de stream.
$opts = [
    'http' => [
        'method' => 'GET', // Método HTTP da requisição.
        'timeout' => 15,   // Tempo máximo de espera em segundos (evita travamentos).
        'header' => "User-Agent: Mozilla/5.0\r\n" // Define um User-Agent comum.
        // A ReceitaWS bloqueia requisições sem User-Agent ou com User-Agent de robôs.
        // Colocar "Mozilla/5.0" simula um navegador comum, aumentando a chance de sucesso.
    ]
];

// Cria o "contexto" de stream que será usado na função file_get_contents.
// Esse contexto contém todas as configurações de cabeçalho, timeout, etc.
$context = stream_context_create($opts);

// 6. EXECUÇÃO DA CHAMADA À API EXTERNA
// --------------------------------------------------------------
// @file_get_contents: Faz a requisição GET para a URL configurada.
// O '@' (operador de supressão de erro) é usado para evitar que warnings
// (como falha de conexão) apareçam na tela. Em vez disso, tratamos o retorno.
// Parâmetros:
//   - $url: O destino da requisição.
//   - false: Não usa include_path.
//   - $context: As opções configuradas acima (timeout, headers, etc.).
$result = @file_get_contents($url, false, $context);

// 7. TRATAMENTO DE ERRO NA REQUISIÇÃO EXTERNA
// --------------------------------------------------------------
// Se a função retornar false (falha na conexão, timeout, API fora do ar, etc.)
if ($result === false) {
    // Retorna um JSON amigável informando que o serviço está indisponível.
    // Isso é melhor do que deixar o frontend com uma mensagem de erro genérica.
    echo json_encode(['status' => 'ERROR', 'message' => 'Serviço indisponível']);
    exit;
}

// 8. SUCESSO: RETORNA O RESULTADO BRUTO DA API EXTERNA
// --------------------------------------------------------------
// Se chegou aqui, a ReceitaWS respondeu com sucesso.
// O $result já vem da API externa no formato JSON (ex: contendo nome, 
// atividade econômica, endereço, situação cadastral, etc.).
// Nós simplesmente repassamos esse JSON diretamente para o cliente (frontend).
// 
// ATENÇÃO: Não estamos alterando a estrutura dos dados, apenas encaminhando.
// Isso mantém o frontend desacoplado, podendo tratar os campos como preferir.
echo $result;