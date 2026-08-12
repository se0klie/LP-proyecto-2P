<?php

/**
 * Endpoint: POST /api/inscripciones/crear.php
 * Funcionalidad: Inscribirse a un evento (genera pase digital)
 * Responsable: Paulo Tapia
 *
 * Body JSON esperado:
 * { "evento_id": 1 }
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/Response.php';
require_once __DIR__ . '/../../controllers/InscripcionController.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Método no permitido. Use POST.', 405);
}

// Mismo patrón usado en resenas/crear.php: mientras se integra el login
// real, se usa la sesión iniciada por dev-login.php o el estudiante demo (id=2).
$estudianteId = $_SESSION['usuario_id'] ?? 2;

$controller = new InscripcionController();
$controller->inscribir((int) $estudianteId);
