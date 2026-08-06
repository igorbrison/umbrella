<?php
/**
 * Arquivo: Views/partials/dashboard_footer.php
 * Função: Rodapé do painel interno (dashboard) para páginas restritas.
 * 
 * Fecha as tags abertas pelo dashboard_header.php e inclui os
 * scripts JavaScript para funcionamento do dropdown do usuário
 * e do modal de alteração de senha.
 */
?>
        </main><!-- Fim do conteúdo principal -->
    </div><!-- Fim do dashboard-wrapper -->

    <!-- ==================== SCRIPTS DO PAINEL ==================== -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.getElementById('userDropdownToggle');
            const menu = document.getElementById('userDropdownMenu');

            // Dropdown do usuário
            if (toggle && menu) {
                toggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    menu.classList.toggle('show');
                });

                document.addEventListener('click', function(e) {
                    if (!toggle.contains(e.target) && !menu.contains(e.target)) {
                        menu.classList.remove('show');
                    }
                });
            }

            // Modal de alteração de senha
            const modal = document.getElementById('modalSenha');
            const btnAbrir = document.getElementById('btnAlterarSenha');
            const btnFechar = document.getElementById('modalSenhaClose');

            if (btnAbrir && modal) {
                btnAbrir.addEventListener('click', function(e) {
                    e.preventDefault();
                    modal.style.display = 'flex';
                });
            }
            if (btnFechar && modal) {
                btnFechar.addEventListener('click', function() {
                    modal.style.display = 'none';
                });
            }
            window.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>