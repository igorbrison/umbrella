<?php
/**
 * Arquivo: Views/partials/dashboard_footer.php
 * Função: Rodapé do painel interno (dashboard) para páginas restritas.
 * 
 * Fecha as tags abertas pelo dashboard_header.php e inclui os
 * scripts JavaScript para funcionamento do dropdown do usuário,
 * do modal de alteração de senha, do sistema de abas (tabs),
 * do modal de edição de solicitação, do modal de pagamento e
 * do modal de confirmação personalizado.
 */
?>
        </main><!-- Fim do conteúdo principal -->
    </div><!-- Fim do dashboard-wrapper -->

    <!-- ============================================================
    MODAL DE CONFIRMAÇÃO PERSONALIZADO (substitui o confirm nativo)
    ============================================================ -->
    <div id="modalConfirm" class="modal-overlay" style="display:none;">
        <div class="modal-content">
            <span class="modal-close" id="modalConfirmClose">&times;</span>
            <h2>Confirmação</h2>
            <p id="modalConfirmMsg"></p>
            <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px;">
                <button type="button" class="btn btn-limpar" id="modalConfirmCancel">Cancelar</button>
                <button type="button" class="btn-primary" id="modalConfirmOk">Confirmar</button>
            </div>
        </div>
    </div>

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
            const modalSenha = document.getElementById('modalSenha');
            const btnAbrir = document.getElementById('btnAlterarSenha');
            const btnFechar = document.getElementById('modalSenhaClose');

            if (btnAbrir && modalSenha) {
                btnAbrir.addEventListener('click', function(e) {
                    e.preventDefault();
                    modalSenha.style.display = 'flex';
                });
            }
            if (btnFechar && modalSenha) {
                btnFechar.addEventListener('click', function() {
                    modalSenha.style.display = 'none';
                });
            }
            window.addEventListener('click', function(e) {
                if (e.target === modalSenha) {
                    modalSenha.style.display = 'none';
                }
            });

            // ============================================================
            // 3. SISTEMA DE ABAS (TABS) - PARA FORMULÁRIOS
            // ============================================================
            const tabBtns = document.querySelectorAll('.tab-btn');
            const tabPanes = document.querySelectorAll('.tab-pane');

            if (tabBtns.length > 0 && tabPanes.length > 0) {
                function activateTab(index) {
                    tabBtns.forEach(btn => btn.classList.remove('active'));
                    tabPanes.forEach(pane => pane.classList.remove('active'));
                    if (tabBtns[index]) tabBtns[index].classList.add('active');
                    if (tabPanes[index]) tabPanes[index].classList.add('active');
                    localStorage.setItem('activeTab', index);
                }

                tabBtns.forEach((btn, idx) => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        activateTab(idx);
                    });
                });

                const savedTab = localStorage.getItem('activeTab');
                if (savedTab !== null && tabBtns[savedTab]) {
                    activateTab(parseInt(savedTab));
                } else {
                    activateTab(0);
                }
            }

            // ============================================================
            // 4. MODAL DE EDIÇÃO DE SOLICITAÇÃO (FECHAR)
            // ============================================================
            const modalEditar = document.getElementById('modalEditar');
            if (modalEditar) {
                const closeEditar = document.getElementById('modalEditarClose');
                if (closeEditar) {
                    closeEditar.addEventListener('click', function() {
                        modalEditar.style.display = 'none';
                    });
                }
                window.addEventListener('click', function(e) {
                    if (e.target === modalEditar) {
                        modalEditar.style.display = 'none';
                    }
                });
            }

            // ============================================================
            // 5. MODAL DE PAGAMENTO (FECHAR)
            // ============================================================
            const modalPagamento = document.getElementById('modalPagamento');
            if (modalPagamento) {
                const closePagamento = document.getElementById('modalPagamentoClose');
                if (closePagamento) {
                    closePagamento.addEventListener('click', function() {
                        modalPagamento.style.display = 'none';
                    });
                }
                window.addEventListener('click', function(e) {
                    if (e.target === modalPagamento) {
                        modalPagamento.style.display = 'none';
                    }
                });
            }

            // ============================================================
            // 6. MODAL DE CONFIRMAÇÃO PERSONALIZADO
            // ============================================================
            const modalConfirm = document.getElementById('modalConfirm');
            const modalConfirmMsg = document.getElementById('modalConfirmMsg');
            const modalConfirmOk = document.getElementById('modalConfirmOk');
            const modalConfirmCancel = document.getElementById('modalConfirmCancel');
            const modalConfirmClose = document.getElementById('modalConfirmClose');
            let confirmUrl = null;

            function fecharConfirm() {
                modalConfirm.style.display = 'none';
                confirmUrl = null;
            }

            if (modalConfirm && modalConfirmOk && modalConfirmCancel && modalConfirmClose) {
                modalConfirmOk.addEventListener('click', function() {
                    if (confirmUrl) window.location.href = confirmUrl;
                    fecharConfirm();
                });
                modalConfirmCancel.addEventListener('click', fecharConfirm);
                modalConfirmClose.addEventListener('click', fecharConfirm);
                window.addEventListener('click', function(e) {
                    if (e.target === modalConfirm) fecharConfirm();
                });
            }

            // Torna a função global
            window.confirmarAcao = function(mensagem, url) {
                modalConfirmMsg.textContent = mensagem;
                confirmUrl = url;
                modalConfirm.style.display = 'flex';
            };
        });
    </script>
</body>
</html>