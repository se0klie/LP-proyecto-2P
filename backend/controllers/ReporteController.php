<?php

require_once __DIR__ . '/../models/Resena.php';
require_once __DIR__ . '/../helpers/Response.php';

class ReporteController
{
    private Resena $resenaModel;

    public function __construct()
    {
        $this->resenaModel = new Resena();
    }

    public function obtenerReporte(int $eventoId): void
    {
        try {
            $estadisticas = $this->resenaModel->obtenerEstadisticas($eventoId);
            
            if (empty($estadisticas)) {
                Response::error('No se encontraron datos para este evento.', 404);
            }

            // Calculamos porcentaje de asistencia basándonos en quienes dejaron reseña 
            // (puedes ajustar esta lógica matemática si luego añaden un pase de lista real)
            $inscritos = (int) $estadisticas['total_inscritos'];
            $asistencias = (int) $estadisticas['total_resenas'];
            $porcentajeAsistencia = $inscritos > 0 ? ROUND(($asistencias / $inscritos) * 100, 1) : 0;

            $datosReporte = [
                'total_inscritos' => $inscritos,
                'asistencia_final' => $asistencias,
                'porcentaje_asistencia' => $porcentajeAsistencia . '%',
                'valoracion_promedio' => $estadisticas['valoracion_promedio'] . ' / 5'
            ];

            Response::success($datosReporte, 'Reporte estadístico generado correctamente.');
        } catch (Throwable $e) {
            error_log('Error al generar reporte: ' . $e->getMessage());
            Response::error('Error al generar el reporte estadístico.', 500);
        }
    }
}