<?php

require_once __DIR__ . '/../helpers/Response.php';

/**
 * Clase Auth
 *
 * El login/registro de usuarios NO pertenece a este módulo
 * (corresponde al módulo de inscripción/pases digitales).
 * Este middleware asume que, tras autenticarse, el sistema
 * deja disponibles en la sesión PHP:
 *   $_SESSION['usuario_id']
 *   $_SESSION['rol']
 *
 * Se deja aquí una verificación mínima para proteger los
 * endpoints de creación y administración de eventos, de modo
 * que el módulo sea funcional de forma independiente durante
 * el desarrollo/pruebas.
 */
class Auth
{
    /**
     * Verifica que exista una sesión activa y retorna el id del usuario.
     * Si no hay sesión válida, corta la ejecución con un 401.
     */
    public static function requireLogin(): int
    {
        if (empty($_SESSION['usuario_id'])) {
            Response::error('No autenticado. Debe iniciar sesión.', 401);
        }

        return (int) $_SESSION['usuario_id'];
    }

    /**
     * Verifica que el usuario autenticado tenga rol de organizador.
     * Corta la ejecución con 401/403 si no cumple.
     */
    public static function requireOrganizador(): int
    {
        $usuarioId = self::requireLogin();

        if (($_SESSION['rol'] ?? null) !== 'organizador') {
            Response::error('Acceso restringido. Se requiere rol de organizador.', 403);
        }

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
            Response::success('Sesión cerrada correctamente.');
        }
        return True
}
