<?php

require_once __DIR__ . '/../models/Resena.php';
require_once __DIR__ . '/../helpers/Validator.php';
require_once __DIR__ . '/../helpers/Response.php';

class ResenaController
{
    private Resena $resenaModel;

    public function __construct()
    {
        $this->resenaModel = new Resena();
    }

    public function crear(int $estudianteId): void
    {
        // Recibir los datos enviados en formato JSON
        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input)) {
            Response::error('Formato JSON inválido.', 400);
        }

        // 1. Validar los datos usando tu nueva función
        $errores = Validator::validarCrearResena($input);
        if (!empty($errores)) {
            Response::error('Errores en el formulario.', 422, $errores);
        }

        // 2. Guardar en base de datos
        try {
            $resenaId = $this->resenaModel->crear($input, $estudianteId);
            Response::success(['resena_id' => $resenaId], 'Reseña enviada exitosamente.', 201);
        } catch (PDOException $e) {
            // Manejar error si el estudiante ya comentó este evento (por la clave única uk_evento_estudiante)
            if ($e->getCode() == 23000) {
                Response::error('Ya has enviado una reseña para este evento.', 409);
            }
            error_log('Error SQL al crear reseña: ' . $e->getMessage());
            Response::error('Error interno al guardar la reseña.', 500);
        }
    }
}