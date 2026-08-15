<?php

require_once __DIR__ . '/../helpers/Response.php';

class Auth
{
    public static function requireLogin(): int
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (
            empty($_SESSION['logged_in']) ||
            $_SESSION['logged_in'] !== true ||
            empty($_SESSION['user_id'])
        ) {
            Response::error('No autenticado. Debe iniciar sesión.', 401);
        }

        return (int) $_SESSION['user_id'];
    }

    public static function requireOrganizador(): int
    {
        $usuarioId = self::requireLogin();
        return $usuarioId;
    }

    public static function userLogout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        Response::success(null, 'Sesión cerrada correctamente.');
    }
}