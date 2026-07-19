<?php

/**
 * Arquivo: routes/web.php
 * Função: DEFINIÇÃO DE ROTAS da aplicação.
 * 
 * Este arquivo contém todos os endpoints (URLs) que a aplicação responde.
 * Ele utiliza o roteador da biblioteca Bramus\Router para mapear cada URL
 * a uma ação específica (geralmente um método de um Controller).
 * 
 * Fluxo:
 *   1. O index.php (Front Controller) instancia o Router e inclui este arquivo.
 *   2. As rotas são definidas aqui, associando padrões de URL a funções anônimas.
 *   3. O index.php chama $router->run() para processar a requisição atual.
 * 
 * Organização:
 *   - Rota raiz: redireciona para a lista de representantes.
 *   - Grupo /representantes: agrupa todas as rotas relacionadas à entidade.
 */

// 1. ROTA RAIZ (PÁGINA INICIAL)
// --------------------------------------------------------------
// A rota mais simples: quando o usuário acessa a raiz do site (ex: http://localhost/),
// ele é automaticamente redirecionado para a lista de representantes.
// O 'exit' garante que o script pare após o redirecionamento.
$router->get('/', function() {
    header('Location: /representantes');
    exit;
});

// 2. GRUPO DE ROTAS: /representantes
// --------------------------------------------------------------
// O método 'mount' cria um grupo de rotas com um prefixo comum.
// Todas as rotas definidas dentro deste grupo terão a URL base "/representantes".
// 
// Vantagens do agrupamento:
//   - Organização: rotas relacionadas ficam juntas.
//   - Facilidade de manutenção: se o prefixo mudar, altera-se apenas um local.
//   - Clareza: fica explícito que todas essas rotas lidam com a entidade "Representante".
// 
// A sintaxe 'use ($router)' é necessária para que a variável $router esteja
// acessível dentro da função anônima (escopo de variável).
$router->mount('/representantes', function() use ($router) {

    // 2.1. LISTAR TODOS OS REPRESENTANTES
    // --------------------------------------------------------------
    // Rota: GET /representantes
    // Ação: Chama o método 'index()' do RepresentanteController.
    // Função: Exibe a tabela com todos os representantes cadastrados.
    $router->get('/', function() {
        // Inclui o arquivo do Controller (se ainda não foi carregado).
        // Isso é feito dentro de cada rota para carregar apenas quando necessário.
        require_once __DIR__ . '/../app/Controllers/RepresentanteController.php';
        // Instancia o Controller e executa o método 'index'.
        (new RepresentanteController())->index();
    });

    // 2.2. EXIBIR FORMULÁRIO DE CRIAÇÃO
    // --------------------------------------------------------------
    // Rota: GET /representantes/criar
    // Ação: Chama o método 'criar()' do RepresentanteController.
    // Função: Exibe o formulário em branco para cadastrar um novo representante.
    $router->get('/criar', function() {
        require_once __DIR__ . '/../app/Controllers/RepresentanteController.php';
        (new RepresentanteController())->criar();
    });

    // 2.3. PROCESSAR O SALVAMENTO (INSERIR OU ATUALIZAR)
    // --------------------------------------------------------------
    // Rota: POST /representantes/salvar
    // Ação: Chama o método 'salvar()' do RepresentanteController.
    // Função: Recebe os dados do formulário (via POST), valida e salva no banco.
    // 
    // OBS: Este mesmo método trata tanto a criação quanto a edição,
    // diferenciando pela presença do campo 'id' no POST.
    $router->post('/salvar', function() {
        require_once __DIR__ . '/../app/Controllers/RepresentanteController.php';
        (new RepresentanteController())->salvar();
    });

    // 2.4. EXIBIR FORMULÁRIO DE EDIÇÃO (PREECHIDO)
    // --------------------------------------------------------------
    // Rota: GET /representantes/editar/{id}
    // Padrão: '/(\d+)' captura um ou mais dígitos como parâmetro.
    // Ação: Chama o método 'editar($id)' do RepresentanteController.
    // Função: Busca o representante pelo ID e exibe o formulário com os dados preenchidos.
    // 
    // O parâmetro capturado ($id) é passado automaticamente para a função anônima.
    $router->get('/editar/(\d+)', function($id) {
        require_once __DIR__ . '/../app/Controllers/RepresentanteController.php';
        (new RepresentanteController())->editar($id);
    });

    // 2.5. ALTERNAR STATUS (ATIVAR / DESATIVAR)
    // --------------------------------------------------------------
    // Rota: GET /representantes/status/{id}
    // Ação: Chama o método 'status($id)' do RepresentanteController.
    // Função: Inverte o campo 'ativo' do representante (0 vira 1, e vice-versa).
    // 
    // Normalmente, uma alteração de estado seria feita via POST/PUT,
    // mas por simplicidade, usamos GET (comum em sistemas mais antigos).
    $router->get('/status/(\d+)', function($id) {
        require_once __DIR__ . '/../app/Controllers/RepresentanteController.php';
        (new RepresentanteController())->status($id);
    });

    // 2.6. EXCLUIR PERMANENTEMENTE
    // --------------------------------------------------------------
    // Rota: GET /representantes/excluir/{id}
    // Ação: Chama o método 'excluir($id)' do RepresentanteController.
    // Função: Remove o representante do banco de dados (DELETE físico).
    // 
    // ATENÇÃO: Exclusão irreversível. Em produção, considere usar soft delete.
    // O link que leva a esta rota deve ter um JavaScript de confirmação.
    $router->get('/excluir/(\d+)', function($id) {
        require_once __DIR__ . '/../app/Controllers/RepresentanteController.php';
        (new RepresentanteController())->excluir($id);
    });

});

// Fim do arquivo de rotas.
// O index.php chamará $router->run() após a inclusão deste arquivo.