<?php
/**
 * Arquivo: Controllers/SenhaController.php
 * Função: Controlador para alteração de senha do próprio usuário (admin ou representante).
 * 
 * Processa requisições POST do modal de alteração de senha.
 */
class SenhaController {

    public function alterar(string $perfil): void {
        $senhaAtual = $_POST['senha_atual'] ?? '';
        $novaSenha = $_POST['nova_senha'] ?? '';
        $confirmarSenha = $_POST['confirmar_senha'] ?? '';

        if (empty($senhaAtual) || empty($novaSenha) || empty($confirmarSenha)) {
            $_SESSION['erro_senha'] = 'Preencha todos os campos.';
            $this->redirecionar($perfil);
            return;
        }
        if ($novaSenha !== $confirmarSenha) {
            $_SESSION['erro_senha'] = 'As senhas não conferem.';
            $this->redirecionar($perfil);
            return;
        }

        $pdo = Database::getInstance();
        $tabela = $perfil === 'admin' ? 'administradores' : 'representantes';
        $idColuna = $perfil === 'admin' ? 'admin_id' : 'representante_id';
        $emailColuna = $perfil === 'admin' ? 'admin_email' : 'representante_email';

        // Busca o usuário pelo email na sessão
        $stmt = $pdo->prepare("SELECT senha FROM $tabela WHERE email = :email");
        $stmt->execute([':email' => $_SESSION[$emailColuna] ?? '']);
        $usuario = $stmt->fetch();

        if (!$usuario || !password_verify($senhaAtual, $usuario['senha'])) {
            $_SESSION['erro_senha'] = 'Senha atual incorreta.';
            $this->redirecionar($perfil);
            return;
        }

        $hash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE $tabela SET senha = :senha WHERE email = :email");
        $stmt->execute([':senha' => $hash, ':email' => $_SESSION[$emailColuna]]);

        $_SESSION['sucesso_senha'] = 'Senha alterada com sucesso!';
        $this->redirecionar($perfil);
    }

    private function redirecionar(string $perfil): void {
        $rota = $perfil === 'admin' ? '/admin/perfil' : '/painel/perfil';
        header("Location: $rota");
        exit;
    }
}