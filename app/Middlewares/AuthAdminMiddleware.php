<?php
class AuthAdminMiddleware {
    public static function verificar(): void {
        if (!isset($_SESSION['admin_id'])) {
            header('Location: /admin/login');
            exit;
        }
    }
}