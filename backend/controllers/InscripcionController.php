<?php

require_once __DIR__ . '/../models/Catalogo.php';
require_once __DIR__ . '/../models/Inscripcion.php';
require_once __DIR__ . '/../helpers/Response.php';

/**
 * Clase InscripcionController
 * Módulo: Exploración de catálogo, inscripción y pase digital
 * Responsable: Paulo Tapia
 *
 * Funcionalidades:
 *   - Explorar catálogo de eventos (listar / detalle)
 *   - Inscribirse a un evento (genera pase digital)
 */
class InscripcionController
{
    private Catalogo $catalogoModel;
    private Inscripcion $inscripcionModel;

    public function __construct()
    {
        $this->catalogoModel = new Catalogo();
        $this->inscripcionModel = new Inscripcion();
    }

    /**
     * GET /api/eventos/catalogo.php
     * Query params opcionales:
     *   ?busqueda=texto
     *   ?categoria_id=1
     */
    public function catalogo(): void
    {
        $busqueda = isset($_GET['busqueda']) ? trim((string) $_GET['busqueda']) : null;
        $categoriaId = isset($_GET['categoria_id']) && $_GET['categoria_id'] !== ''
            ? (int) $_GET['categoria_id']
            : null;

        try {
            $eventos = $this->catalogoModel->listar($busqueda ?: null, $categoriaId);
            $categorias = $this->catalogoModel->listarCategorias();

            Response::success([
                'total'      => count($eventos),
                'eventos'    => $eventos,
                'categorias' => $categorias,
            ], 'Catálogo de eventos obtenido correctamente.');
        } catch (Throwable $e) {
            error_log('Error al obtener catálogo de eventos: ' . $e->getMessage());
            Response::error('Ocurrió un error al obtener el catálogo de eventos.', 500);
        }
    }

    /**
     * GET /api/eventos/detalle.php?id=1
     */
    public function detalle(int $eventoId): void
    {
        if ($eventoId <= 0) {
            Response::error('Falta el ID del evento o es inválido.', 400);
        }

        try {
            $evento = $this->catalogoModel->detalle($eventoId);

            if ($evento === null) {
                Response::error('El evento solicitado no existe.', 404);
            }

            Response::success($evento, 'Detalle del evento obtenido correctamente.');
        } catch (Throwable $e) {
            error_log('Error al obtener detalle del evento: ' . $e->getMessage());
            Response::error('Ocurrió un error al obtener el detalle del evento.', 500);
        }
    }

    /**
     * POST /api/inscripciones/crear.php
     * Body JSON esperado:
     * { "evento_id": 1 }
     */
    public function inscribir(int $estudianteId): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input)) {
            Response::error('El cuerpo de la petición debe ser un JSON válido.', 400);
        }

        if (empty($input['evento_id']) || !filter_var($input['evento_id'], FILTER_VALIDATE_INT)) {
            Response::error('Existen errores de validación en los datos enviados.', 422, [
                'evento_id' => 'El ID del evento es obligatorio y debe ser un número entero.',
            ]);
        }

        $eventoId = (int) $input['evento_id'];

        try {
            $resultado = $this->inscripcionModel->inscribir($eventoId, $estudianteId);
            Response::success($resultado, 'Inscripción confirmada. Pase digital generado.', 201);
        } catch (RuntimeException $e) {
            switch ($e->getMessage()) {
                case 'EVENTO_NO_EXISTE':
                    Response::error('El evento solicitado no existe.', 404);
                    break;
                case 'EVENTO_NO_ACTIVO':
                    Response::error('El evento no está disponible para inscripciones.', 409);
                    break;
                case 'CUPOS_AGOTADOS':
                    Response::error('Lo sentimos, el evento ya no tiene cupos disponibles.', 409);
                    break;
                case 'YA_INSCRITO':
                    Response::error('Ya estás inscrito en este evento.', 409);
                    break;
                default:
                    error_log('Error al inscribir: ' . $e->getMessage());
                    Response::error('Ocurrió un error al procesar la inscripción.', 500);
            }
        } catch (Throwable $e) {
            error_log('Error al inscribir: ' . $e->getMessage());
            Response::error('Ocurrió un error al procesar la inscripción.', 500);
        }
    }
}
