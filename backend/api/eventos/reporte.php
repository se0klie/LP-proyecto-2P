<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/Response.php';
require_once __DIR__ . '/../../controllers/ReporteController.php';

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('Método no permitido. Use GET.', 405);
}

$eventoId = isset($_GET['evento_id']) ? (int) $_GET['evento_id'] : 0;

if ($eventoId === 0) {
    Response::error('Falta el ID del evento.', 400);
}

$controller = new ReporteController();
$controller->obtenerReporte($eventoId);