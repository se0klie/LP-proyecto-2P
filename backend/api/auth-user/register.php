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
$cargo = trim($data['cargo'] ?? '');
$contrasena = $data['contrasena'] ?? '';

if (!preg_match('/^[^@\s]+@espol\.edu\.ec$/i', $correo)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Debe utilizar un correo institucional @espol.edu.ec.'
    ]);
    exit;
}

$usuario = strtolower(explode('@', $correo)[0]);

$cargosValidos = [
    'estudiante',
    'administrativo',
    'profesor'
];

if (!in_array($cargo, $cargosValidos, true)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Cargo no válido.'
    ]);
    exit;
}

if (strlen($contrasena) < 6) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'La contraseña debe tener al menos 6 caracteres.'
    ]);
    exit;
}

try {

    $stmt = $pdo->prepare(
        'SELECT id FROM usuarios WHERE correo = :correo OR usuario = :usuario LIMIT 1'
    );

    $stmt->execute([
        ':correo' => $correo,
        ':usuario' => $usuario
    ]);

    if ($stmt->fetch()) {
        http_response_code(409);

        echo json_encode([
            'success' => false,
            'message' => 'El usuario ya está registrado.'
        ]);

        exit;
    }

    $hash = password_hash($contrasena, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare(
        'INSERT INTO usuarios
        (usuario, correo, cargo, contrasena)
        VALUES
        (:usuario, :correo, :cargo, :contrasena)'
    );

    $stmt->execute([
        ':usuario' => $usuario,
        ':correo' => $correo,
        ':cargo' => $cargo,
        ':contrasena' => $hash
    ]);

    $usuarioId = (int) $pdo->lastInsertId();

    $_SESSION['usuario_id'] = $usuarioId;
    $_SESSION['usuario'] = $usuario;
    $_SESSION['correo'] = $correo;
    $_SESSION['cargo'] = $cargo;

    echo json_encode([
        'success' => true,
        'message' => 'Usuario registrado correctamente.',
        'usuario' => [
            'id' => $usuarioId,
            'usuario' => $usuario,
            'correo' => $correo,
            'cargo' => $cargo
        ]
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Error al registrar el usuario.',
        'error' => $e->getMessage()
    ]);
}