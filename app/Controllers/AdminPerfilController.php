<?php
/**
 * Arquivo: Controllers/AdminPerfilController.php
 * Função: Controlador para edição do próprio perfil do administrador.
 * 
 * Permite que o admin altere seu nome e email.
 */
require_once __DIR__ . '/../Models/Admin.php';

class AdminPerfilController {

    public function editar(): void {
        $adminModel = new Admin();
        $admin = $adminModel->buscarPorEmail($_SESSION['admin_email'] ?? '');
        if (!$admin) {
            echo "Administrador não encontrado.";
            exit;
        }
        require __DIR__ . '/../Views/admin/perfil/form.php';
    }

    public function salvar(): void {
        $nome = $_POST['nome'] ?? '';
        $email = $_POST['email'] ?? '';
        if (empty($nome) || empty($email)) {
            echo "Preencha todos os campos.";
            exit;
        }
        // Atualiza no banco
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("UPDATE administradores SET nome = :nome, email = :email WHERE id = :id");
        $stmt->execute([':nome' => $nome, ':email' => $email, ':id' => $_SESSION['admin_id']]);

        // Atualiza a sessão
        $_SESSION['admin_nome'] = $nome;
        $_SESSION['admin_email'] = $email;

        header('Location: /admin/perfil?sucesso=1');
        exit;
    }
}