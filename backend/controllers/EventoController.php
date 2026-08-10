<?php

require_once __DIR__ . '/../models/Evento.php';
require_once __DIR__ . '/../helpers/Validator.php';
require_once __DIR__ . '/../helpers/Response.php';

/**
 * Clase EventoController
 * Orquesta la validación, reglas de negocio y respuesta HTTP
 * para las funcionalidades:
 *   - Crear un evento
 *   - Ver panel de organizador
 */
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

        if (!is_array($input)) {
            Response::error('El cuerpo de la petición debe ser un JSON válido.', 400);
        }

        // 1. Validación de formato/reglas básicas
        $errores = Validator::validarCrearEvento($input);
        if (!empty($errores)) {
            Response::error('Existen errores de validación en los datos enviados.', 422, $errores);
        }

        // 2. Validación de integridad referencial (categoría existente)
        if (!$this->eventoModel->categoriaExiste((int) $input['categoria_id'])) {
            Response::error('La categoría seleccionada no existe.', 422, [
                'categoria_id' => 'Categoría inválida.',
            ]);
        }

        // 3. Creación del evento
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
}
