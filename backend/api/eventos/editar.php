<?php

/**
 * Endpoint: PUT /api/eventos/editar.php?id=1
 * Funcionalidad: Editar un evento
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/Response.php';
require_once __DIR__ . '/../../middleware/Auth.php';
require_once __DIR__ . '/../../controllers/EventoController.php';

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    Response::error('Método no permitido. Use PUT.', 405);
}

$organizadorId = Auth::requireOrganizador();

$eventoId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($eventoId <= 0) {
    Response::error('Debe proporcionar un ID de evento válido.', 400);
}

$controller = new EventoController();
$controller->editar($eventoId, $organizadorId);