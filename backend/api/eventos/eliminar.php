<?php

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');



require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/EventoController.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido.'
    ]);

    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'No autenticado.'
    ]);

    exit;
}

$eventoId = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($eventoId <= 0) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'ID de evento inválido.'
    ]);

    exit;
}

$organizadorId = (int) $_SESSION['user_id'];

$controller = new EventoController();

$controller->eliminar(
    $eventoId,
    $organizadorId
);