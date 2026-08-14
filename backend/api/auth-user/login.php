<?php

require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido.'
    ]);

    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$correo = trim($data['correo'] ?? '');
$contrasena = $data['contrasena'] ?? '';

if ($correo === '' || $contrasena === '') {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Correo y contraseña son obligatorios.'
    ]);

    exit;
}

try {

    $stmt = $pdo->prepare(
        'SELECT id, usuario, correo, cargo, contrasena
         FROM usuarios
         WHERE correo = :correo
         LIMIT 1'
    );

    $stmt->execute([
        ':correo' => $correo
    ]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario || !password_verify($contrasena, $usuario['contrasena'])) {
        http_response_code(401);

        echo json_encode([
            'success' => false,
            'message' => 'Correo o contraseña incorrectos.'
        ]);

        exit;
    }

    session_regenerate_id(true);

    $_SESSION['usuario_id'] = (int) $usuario['id'];
    $_SESSION['usuario'] = $usuario['usuario'];
    $_SESSION['correo'] = $usuario['correo'];
    $_SESSION['cargo'] = $usuario['cargo'];

    echo json_encode([
        'success' => true,
        'message' => 'Inicio de sesión exitoso.',
        'usuario' => [
            'id' => (int) $usuario['id'],
            'usuario' => $usuario['usuario'],
            'correo' => $usuario['correo'],
            'cargo' => $usuario['cargo']
        ]
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Error al iniciar sesión.'
    ]);
}