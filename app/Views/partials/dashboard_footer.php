<?php
/**
 * Arquivo: Views/partials/dashboard_footer.php
 * Função: Rodapé do painel interno (dashboard) para páginas restritas.
 * 
 * Fecha as tags abertas pelo dashboard_header.php e inclui os
 * scripts JavaScript para funcionamento do dropdown do usuário,
 * do modal de alteração de senha e do sistema de abas (tabs).
 * 
 * Os scripts são colocados aqui para garantir que todos os elementos
 * do DOM já tenham sido carregados antes da execução.
 */
?>
        </main><!-- Fim do conteúdo principal -->
    </div><!-- Fim do dashboard-wrapper -->

    <!-- ============================================================
    SCRIPTS DO PAINEL
    ============================================================ -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ============================================================
            // 1. DROPDOWN DO USUÁRIO
            // ============================================================
            const toggle = document.getElementById('userDropdownToggle');
            const menu = document.getElementById('userDropdownMenu');

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

            // ============================================================
            // 2. MODAL DE ALTERAÇÃO DE SENHA
            // ============================================================
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

            // ============================================================
            // 3. SISTEMA DE ABAS (TABS) - PARA FORMULÁRIOS
            // ============================================================
            const tabBtns = document.querySelectorAll('.tab-btn');
            const tabPanes = document.querySelectorAll('.tab-pane');

            if (tabBtns.length > 0 && tabPanes.length > 0) {
                // Função para ativar uma aba
                function activateTab(index) {
                    // Remove a classe 'active' de todos os botões e painéis
                    tabBtns.forEach(btn => btn.classList.remove('active'));
                    tabPanes.forEach(pane => pane.classList.remove('active'));

                    // Adiciona 'active' ao botão e painel correspondentes
                    if (tabBtns[index]) tabBtns[index].classList.add('active');
                    if (tabPanes[index]) tabPanes[index].classList.add('active');

                    // Armazena a aba ativa no localStorage para lembrar após recarregar
                    localStorage.setItem('activeTab', index);
                }

                // Adiciona evento de clique a cada botão
                tabBtns.forEach((btn, idx) => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        activateTab(idx);
                    });
                });

                // Restaura a última aba ativa (se existir)
                const savedTab = localStorage.getItem('activeTab');
                if (savedTab !== null && tabBtns[savedTab]) {
                    activateTab(parseInt(savedTab));
                } else {
                    // Ativa a primeira aba por padrão
                    activateTab(0);
                }
            }
        });
    </script>
</body>
</html>