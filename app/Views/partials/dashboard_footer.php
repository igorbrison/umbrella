<?php
/**
 * Arquivo: Views/partials/dashboard_footer.php
 * Função: Rodapé do painel interno (dashboard) para páginas restritas.
 * 
 * Responsável por:
 *   - Fechar as tags HTML abertas pelo dashboard_header.php.
 *       • Fecha o elemento <main> (área de conteúdo principal).
 *       • Fecha a <div> do wrapper (dashboard-wrapper).
 *       • Fecha as tags <body> e <html>.
 * 
 * Deve ser utilizado em conjunto com o arquivo dashboard_header.php,
 * que inicia a estrutura do painel.
 * 
 * Uso: Incluir no final de cada view interna do painel.
 * Exemplo:
 *   require __DIR__ . '/../partials/dashboard_header.php';
 *   // conteúdo da página...
 *   require __DIR__ . '/../partials/dashboard_footer.php';
 */
?>
        </main><!-- Fim do conteúdo principal -->
    </div><!-- Fim do dashboard-wrapper -->
</body>
</html>