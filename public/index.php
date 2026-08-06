<?php

/**
 * Arquivo: index.php
 * Função: FRONT CONTROLLER (Ponto de entrada único) da aplicação.
 * Todas as requisições HTTP (ex: http://meusite.com/) passam primeiro por aqui.
 * Ele orquestra o carregamento das dependências, configurações e define qual 
 * ação deve ser executada com base na URL acessada.
 */

// 1. CARREGAMENTO DO AUTOLOAD (Composer)
// --------------------------------------------------------------
// __DIR__ garante o caminho absoluto da pasta atual (public/).
// '/../vendor/autoload.php' sobe um nível (para a raiz do projeto) e acessa o autoload.
// O autoload faz a mágica de incluir automaticamente as classes das bibliotecas 
// instaladas (como o "bramus/router") e também as suas próprias classes do projeto 
// (se configurado no composer.json com psr-4).
require_once __DIR__ . '/../vendor/autoload.php';

// 2. CARREGAMENTO DAS CONFIGURAÇÕES GERAIS
// --------------------------------------------------------------
// Inclui o arquivo que contém as configurações sensíveis e globais do sistema.
// Normalmente, esse arquivo define as constantes de conexão com o Banco de Dados 
// (host, nome, usuário, senha), timezone da aplicação, entre outras variáveis.
// Obs: Se você usar variáveis de ambiente (.env), é aqui que elas são carregadas.
require_once __DIR__ . '/../config/config.php';

// 3. INSTANCIAÇÃO DO ROTEADOR
// --------------------------------------------------------------
// Cria um novo objeto da classe Router fornecida pela biblioteca "bramus/router".
// Este objeto será o "maestro" da aplicação: ele vai analisar a URL que o usuário digitou
// e decidir qual Controller ou função PHP deverá ser executada.
$router = new \Bramus\Router\Router();

// 4. CARREGAMENTO DAS ROTAS (MAPEAMENTO URL -> AÇÃO)
// --------------------------------------------------------------
// Inclui o arquivo web.php, que é onde nós definimos todas as rotas da aplicação.
// Exemplo de rotas que estão lá dentro:
//   $router->get('/', 'HomeController@index');   // Rota para a página inicial
//   $router->post('/login', 'AuthController@login'); // Rota para envio de formulário
// Dentro do web.php, passamos a instância $router para configurar tudo.
require_once __DIR__ . '/../routes/web.php';

// 5. EXECUÇÃO DO ROTEADOR (DISPARO DA REQUISIÇÃO)
// --------------------------------------------------------------
// Aqui o roteador finalmente "entra em ação".
// Ele pega a URL atual (ex: /produtos/23) e o método HTTP (GET, POST, PUT...),
// compara com as rotas definidas no web.php, e:
//   - Se encontrar correspondência: Executa a função/Controller correspondente.
//   - Se NÃO encontrar: Automaticamente retorna um erro 404 (Página não encontrada).
// É aqui que a resposta (HTML, JSON, etc.) é gerada e enviada de volta ao navegador.
$router->run();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);