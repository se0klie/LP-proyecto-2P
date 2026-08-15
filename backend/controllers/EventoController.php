<?php

require_once __DIR__ . '/../models/Evento.php';
require_once __DIR__ . '/../helpers/Validator.php';
require_once __DIR__ . '/../helpers/Response.php';

class EventoController
{
    private Evento $eventoModel;

    public function __construct()
    {
        $this->eventoModel = new Evento();
    }

    /**
     * POST /api/eventos/crear
     * Body JSON esperado:
     * {
     *   "titulo": "...",
     *   "descripcion": "...",
     *   "fecha_evento": "YYYY-MM-DD",
     *   "hora_evento": "HH:MM",      (opcional)
     *   "lugar": "...",              (opcional)
     *   "categoria_id": 1,
     *   "aforo_maximo": 100
     * }
     */
    public function crear(int $organizadorId): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        $input = json_decode(file_get_contents('php://input'), true);

        error_log('CREATE EVENT INPUT: ' . print_r($input, true));

        if (!is_array($input)) {
            Response::error('El cuerpo de la petición debe ser un JSON válido.', 400);
        }

        $errores = Validator::validarCrearEvento($input);
        if (!empty($errores)) {
            Response::error('Existen errores de validación en los datos enviados.', 422, $errores);
        }

        if (!$this->eventoModel->categoriaExiste((int) $input['categoria_id'])) {
            Response::error('La categoría seleccionada no existe.', 422, [
                'categoria_id' => 'Categoría inválida.',
            ]);
        }

        try {
            $eventoId = $this->eventoModel->create($input, $organizadorId);
            $evento = $this->eventoModel->findById($eventoId);

            Response::success($evento, 'Evento creado exitosamente.', 201);
        } catch (Throwable $e) {
            error_log('Error al crear evento: ' . $e->getMessage());
            Response::error('Ocurrió un error al registrar el evento. Intente nuevamente.', 500);
        }
    }

    /**
     * GET /api/eventos/panel-organizador
     * Query params opcionales:
     *   ?estado=activo|cancelado|finalizado
     */
    public function panelOrganizador(int $organizadorId): void
    {
        $estado = $_GET['estado'] ?? null;
        $estadosValidos = ['activo', 'cancelado', 'finalizado'];

        if ($estado !== null && !in_array($estado, $estadosValidos, true)) {
            Response::error('El filtro de estado no es válido.', 422, [
                'estado' => 'Valores permitidos: ' . implode(', ', $estadosValidos),
            ]);
        }

        try {
            $eventos = $this->eventoModel->getByOrganizador($organizadorId, $estado);
            $resumen = $this->eventoModel->getResumenOrganizador($organizadorId);

            Response::success([
                'resumen' => $resumen,
                'eventos' => $eventos,
            ], 'Panel del organizador obtenido correctamente.');
        } catch (Throwable $e) {
            error_log('Error al obtener panel de organizador: ' . $e->getMessage());
            Response::error('Ocurrió un error al obtener el panel del organizador.', 500);
        }
    }

    public function editar(int $eventoId, int $organizadorId): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input)) {
            Response::error(
                'El cuerpo de la petición debe ser un JSON válido.',
                400
            );
        }

        try {
            $evento = $this->eventoModel->findById($eventoId);

            if (!$evento) {
                Response::error(
                    'El evento no existe.',
                    404
                );
            }

            if ((int) $evento['organizador_id'] !== $organizadorId) {
                Response::error(
                    'No tienes permiso para editar este evento.',
                    403
                );
            }

            $errores = Validator::validarCrearEvento($input);

            if (!empty($errores)) {
                Response::error(
                    'Existen errores de validación en los datos enviados.',
                    422,
                    $errores
                );
            }

            if (!$this->eventoModel->categoriaExiste(
                (int) $input['categoria_id']
            )) {
                Response::error(
                    'La categoría seleccionada no existe.',
                    422,
                    [
                        'categoria_id' => 'Categoría inválida.'
                    ]
                );
            }

            $this->eventoModel->update(
                $eventoId,
                $input,
                $organizadorId
            );

            $eventoActualizado = $this->eventoModel->findById($eventoId);

            Response::success(
                $eventoActualizado,
                'Evento actualizado exitosamente.'
            );

        } catch (Throwable $e) {

            error_log(
                'Error al editar evento: ' . $e->getMessage()
            );

            Response::error(
                'Ocurrió un error al actualizar el evento.',
                500
            );
        }
    }

    public function eliminar(
        int $eventoId,
        int $organizadorId
    ): void {
        if ($eventoId <= 0) {
            Response::error(
                'El ID del evento es inválido.',
                400
            );
        }

        try {
            $eliminado = $this->eventoModel->delete(
                $eventoId,
                $organizadorId
            );

            if (!$eliminado) {
                Response::error(
                    'El evento no existe o no te pertenece.',
                    404
                );
            }

            Response::success(
                null,
                'Evento eliminado correctamente.'
            );

        } catch (Throwable $e) {
            error_log(
                'Error al eliminar evento: ' .
                $e->getMessage()
            );

            Response::error(
                'Ocurrió un error al eliminar el evento.',
                500
            );
        }
    }

}
