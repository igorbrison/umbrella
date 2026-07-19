<?php

/**
 * Arquivo: consulta_cep.php (ou similar - ex: api/cep.php)
 * Função: ENDPOINT DE API (Proxy) para consulta de CEP.
 * 
 * Este script atua como um "intermediário" entre o frontend (JavaScript) 
 * e a API pública do ViaCEP (https://viacep.com.br).
 * 
 * Motivos para usar um proxy no backend:
 *   1. Evitar problemas de CORS (Cross-Origin Resource Sharing) que bloqueiam 
 *      requisições diretas do navegador para APIs externas.
 *   2. Centralizar a lógica de validação e formatação do CEP.
 *   3. Possibilitar a adição de cache ou logs futuramente.
 *   4. Proteger o frontend de mudanças na API externa (o proxy pode ser ajustado 
 *      sem alterar o código do cliente).
 * 
 * Formato de retorno: Sempre JSON (seja sucesso ou erro).
 * 
 * Observação: O ViaCEP retorna um JSON com campo "erro": true quando o CEP não existe.
 * Este script mantém esse comportamento original, mas também trata erros de rede.
 */

// 1. DEFINIÇÃO DO HEADER DE RESPOSTA
// --------------------------------------------------------------
// Informa ao navegador que o conteúdo retornado será um JSON codificado em UTF-8.
// Isso garante que caracteres especiais (ç, ã, etc.) sejam exibidos corretamente.
header('Content-Type: application/json; charset=utf-8');

// 2. VALIDAÇÃO DO PARÂMETRO OBRIGATÓRIO
// --------------------------------------------------------------
// Verifica se a variável 'cep' foi enviada via URL (método GET).
// Exemplo de requisição esperada: GET /consulta_cep.php?cep=01001000
if (!isset($_GET['cep'])) {
    // Retorna um JSON com erro e interrompe a execução.
    // Usamos a chave 'erro' para manter compatibilidade com o padrão do ViaCEP.
    echo json_encode(['erro' => true, 'message' => 'CEP não informado']);
    exit;
}

// 3. LIMPEZA E FORMATAÇÃO DO CEP
// --------------------------------------------------------------
// Remove todos os caracteres que NÃO são dígitos (0-9).
// Isso trata entradas como "01001-000" ou "01001 000",
// transformando-as em uma string limpa de 8 números.
$cep = preg_replace('/\D/', '', $_GET['cep']);

// 4. VALIDAÇÃO DO TAMANHO
// --------------------------------------------------------------
// O CEP válido no Brasil tem exatamente 8 dígitos numéricos.
// A validação de existência será feita pela API externa (ViaCEP),
// mas já verificamos o comprimento para evitar chamadas desnecessárias.
if (strlen($cep) !== 8) {
    echo json_encode(['erro' => true, 'message' => 'CEP inválido']);
    exit;
}

// 5. CONFIGURAÇÃO DA REQUISIÇÃO HTTP PARA A API EXTERNA
// --------------------------------------------------------------
// Monta a URL da API pública do ViaCEP.
// Exemplo: https://viacep.com.br/ws/01001000/json/
// O ViaCEP retorna um JSON com os dados do endereço ou um objeto com "erro": true.
$url = 'https://viacep.com.br/ws/' . $cep . '/json/';

// Configura as opções da requisição HTTP usando um array.
// Este array será convertido em um "contexto" de stream.
$opts = [
    'http' => [
        'method' => 'GET',    // Método HTTP da requisição.
        'timeout' => 10,      // Tempo máximo de espera em segundos (evita travamentos).
        'header' => "User-Agent: Mozilla/5.0\r\n" // Define um User-Agent comum.
        // O ViaCEP não bloqueia requisições sem User-Agent, mas é uma boa prática
        // definir um para evitar possíveis bloqueios futuros.
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
    echo json_encode(['erro' => true, 'message' => 'Serviço indisponível']);
    exit;
}

// 8. SUCESSO: RETORNA O RESULTADO BRUTO DA API EXTERNA
// --------------------------------------------------------------
// Se chegou aqui, o ViaCEP respondeu com sucesso (status HTTP 200).
// O $result já vem da API externa no formato JSON.
// 
// ATENÇÃO: O ViaCEP pode retornar um JSON com "erro": true se o CEP não existir.
// Nós simplesmente repassamos esse JSON diretamente para o cliente (frontend),
// permitindo que ele trate tanto os dados do endereço quanto a indicação de erro.
// 
// Exemplo de retorno do ViaCEP para CEP válido:
// {
//   "cep": "01001-000",
//   "logradouro": "Praça da Sé",
//   "complemento": "lado ímpar",
//   "bairro": "Sé",
//   "localidade": "São Paulo",
//   "uf": "SP",
//   "ibge": "3550308",
//   "gia": "1004",
//   "ddd": "11",
//   "siafi": "7107"
// }
// 
// Exemplo para CEP inexistente:
// {
//   "erro": true
// }
// 
// O frontend deve verificar a presença do campo "erro" para saber se a consulta foi bem-sucedida.
echo $result;