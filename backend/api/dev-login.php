<?php

/**
 * ⚠️ SOLO PARA DESARROLLO / PRUEBAS ⚠️
 *
 * El login real pertenece al módulo de "exploración e inscripción"
 * (Responsable: Paulo Tapia). Este script simula una sesión de
 * organizador autenticado para poder probar los endpoints de este
 * módulo (crear evento / panel de organizador) de forma aislada.
 *
 * ELIMINAR este archivo antes de integrar con el módulo de login real
 * o antes de desplegar a producción.
 *
 * Uso: GET /api/dev-login.php?usuario_id=1
 */

require_once __DIR__ . '/../config/config.php';

if (APP_ENV === 'production') {
    http_response_code(404);
    exit;
}

$usuarioId = isset($_GET['usuario_id']) ? (int) $_GET['usuario_id'] : 1;

$_SESSION['usuario_id'] = $usuarioId;
$_SESSION['rol'] = 'organizador';

header('Content-Type: application/json; charset=UTF-8');
echo json_encode([
    'success' => true,
    'message' => "Sesión de prueba iniciada como organizador (usuario_id={$usuarioId}).",
]);
