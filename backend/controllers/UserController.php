<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/Validator.php';

class UserController
{
    private User $user;

    public function __construct()
    {
        $this->user = new User();
    }

    public function register(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!is_array($data)) {
                http_response_code(400);

                echo json_encode([
                    'success' => false,
                    'message' => 'El cuerpo de la petición no es válido.'
                ]);

                return;
            }

            $errores = Validator::validarRegistro($data);

            if (!empty($errores)) {
                http_response_code(422);

                echo json_encode([
                    'success' => false,
                    'message' => 'Existen errores de validación.',
                    'errors' => $errores
                ]);

                return;
            }

            $correo = trim($data['correo']);

            if ($this->user->usuarioExiste($correo)) {
                http_response_code(409);

                echo json_encode([
                    'success' => false,
                    'message' => 'El correo ya está registrado.'
                ]);

                return;
            }

            // usuario = parte del correo antes del @
            $usuario = explode('@', $correo)[0];

            $id = $this->user->create([
                'usuario' => $usuario,
                'correo' => $correo,
                'contrasena' => password_hash(
                    $data['contrasena'],
                    PASSWORD_DEFAULT
                ),
                'cargo' => $data['cargo']
            ]);

            http_response_code(201);

            echo json_encode([
                'success' => true,
                'message' => 'Usuario registrado correctamente.',
                'data' => [
                    'id' => $id,
                    'usuario' => $usuario,
                    'correo' => $correo,
                    'cargo' => $data['cargo']
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
    }

    public function login(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!is_array($data)) {
                http_response_code(400);

                echo json_encode([
                    'success' => false,
                    'message' => 'El cuerpo de la petición no es válido.'
                ]);

                return;
            }

            $errores = Validator::validarLogin($data);

            if (!empty($errores)) {
                http_response_code(422);

                echo json_encode([
                    'success' => false,
                    'message' => 'Existen errores de validación.',
                    'errors' => $errores
                ]);

                return;
            }

            $correo = trim($data['correo']);

            $usuario = $this->user->findByCorreo($correo);

            if (!$usuario || !password_verify(
                $data['contrasena'],
                $usuario['contrasena']
            )) {
                http_response_code(401);

                echo json_encode([
                    'success' => false,
                    'message' => 'Correo o contraseña incorrectos.'
                ]);

                return;
            }


            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['usuario'] = $usuario['usuario'];
            $_SESSION['correo'] = $usuario['correo'];
            $_SESSION['cargo'] = $usuario['cargo'];

            echo json_encode([
                'success' => true,
                'message' => 'Inicio de sesión exitoso.',
                'data' => [
                    'id' => $usuario['id'],
                    'usuario' => $usuario['usuario'],
                    'correo' => $usuario['correo'],
                    'cargo' => $usuario['cargo']
                ]
            ]);

        } catch (PDOException $e) {

            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Error al iniciar sesión.',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function logout(): void
    {
        $_SESSION = [];

        session_destroy();

        echo json_encode([
            'success' => true,
            'message' => 'Sesión cerrada correctamente.'
        ]);
    }

    public function me(): void
    {
        if (
            !isset($_SESSION['logged_in']) ||
            $_SESSION['logged_in'] !== true
        ) {
            http_response_code(401);

            echo json_encode([
                'success' => false,
                'message' => 'No hay una sesión activa.'
            ]);

            return;
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'id' => $_SESSION['user_id'],
                'usuario' => $_SESSION['usuario'],
                'correo' => $_SESSION['correo'],
                'cargo' => $_SESSION['cargo']
            ]
        ]);
    }
}