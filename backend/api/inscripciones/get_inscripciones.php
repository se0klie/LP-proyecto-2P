<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/InscripcionController.php';


header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}



try {

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);

        echo json_encode([
            'success' => false,
            'message' => 'Método no permitido'
        ]);

        exit;
    }

    if (!isset($_GET['estudiante_id'])) {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Falta estudiante_id'
        ]);

        exit;
    }

    $estudianteId = filter_var(
        $_GET['estudiante_id'],
        FILTER_VALIDATE_INT
    );

    if ($estudianteId === false || $estudianteId <= 0) {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'estudiante_id inválido'
        ]);

        exit;
    }

    $controller = new InscripcionController();

    $inscripciones = $controller->getByEstudiante($estudianteId);

    echo json_encode([
        'success' => true,
        'data' => $inscripciones
    ]);

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}