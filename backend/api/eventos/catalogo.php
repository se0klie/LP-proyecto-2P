<?php

/**
 * Endpoint: GET /api/eventos/catalogo.php
 * Funcionalidad: Explorar catálogo de eventos
 * Responsable: Paulo Tapia
 *
 * Query params opcionales:
 *   ?busqueda=texto
 *   ?categoria_id=1
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/Response.php';
require_once __DIR__ . '/../../controllers/InscripcionController.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('Método no permitido. Use GET.', 405);
}

$controller = new InscripcionController();
$controller->catalogo();
