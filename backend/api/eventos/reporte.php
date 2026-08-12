<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/Response.php';
require_once __DIR__ . '/../../controllers/ReporteController.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('Método no permitido. Use GET.', 405);
}

// Necesitamos saber de qué evento queremos el reporte
$eventoId = isset($_GET['evento_id']) ? (int) $_GET['evento_id'] : 0;

if ($eventoId === 0) {
    Response::error('Falta el ID del evento.', 400);
}

$controller = new ReporteController();
$controller->obtenerReporte($eventoId);