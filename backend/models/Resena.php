<?php

require_once __DIR__ . '/../config/Database.php';

class Resena
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Guarda una nueva reseña en la base de datos.
     */
    public function crear(array $data, int $estudianteId): int
    {
        $sql = 'INSERT INTO resenas (evento_id, estudiante_id, calificacion, comentario)
                VALUES (:evento_id, :estudiante_id, :calificacion, :comentario)';

        $stmt = $this->db->prepare($sql);
        
        $stmt->execute([
            'evento_id'     => (int) $data['evento_id'],
            'estudiante_id' => $estudianteId,
            'calificacion'  => (int) $data['calificacion'],
            'comentario'    => $data['comentario'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Obtiene los datos para el reporte estadístico de un evento.
     */
    public function obtenerEstadisticas(int $eventoId): array
    {
        // Traemos el aforo actual (inscritos) del evento y calculamos promedios con las reseñas
        $sql = 'SELECT 
                    e.aforo_actual AS total_inscritos,
                    COUNT(r.id) AS total_resenas,
                    COALESCE(ROUND(AVG(r.calificacion), 1), 0) AS valoracion_promedio
                FROM eventos e
                LEFT JOIN resenas r ON e.id = r.evento_id
                WHERE e.id = :evento_id
                GROUP BY e.id';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['evento_id' => $eventoId]);

        return $stmt->fetch() ?: [];
    }
}