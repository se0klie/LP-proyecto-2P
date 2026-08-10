<?php

require_once __DIR__ . '/../config/Database.php';

/**
 * Clase Evento
 * Encapsula el acceso a datos de la tabla `eventos`.
 */
class Evento
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Verifica que una categoría exista.
     */
    public function categoriaExiste(int $categoriaId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM categorias WHERE id = :id');
        $stmt->execute(['id' => $categoriaId]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Crea un nuevo evento en el sistema.
     *
     * @param array $data       Datos ya validados del evento.
     * @param int   $organizadorId  Id del organizador autenticado.
     * @return int  Id del evento recién creado.
     */
    public function create(array $data, int $organizadorId): int
    {
        $sql = 'INSERT INTO eventos
                    (titulo, descripcion, fecha_evento, hora_evento, lugar,
                     categoria_id, organizador_id, aforo_maximo, aforo_actual, estado)
                VALUES
                    (:titulo, :descripcion, :fecha_evento, :hora_evento, :lugar,
                     :categoria_id, :organizador_id, :aforo_maximo, 0, "activo")';

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'titulo'         => trim($data['titulo']),
            'descripcion'    => trim($data['descripcion']),
            'fecha_evento'   => $data['fecha_evento'],
            'hora_evento'    => $data['hora_evento'] ?? null,
            'lugar'          => $data['lugar'] ?? null,
            'categoria_id'   => (int) $data['categoria_id'],
            'organizador_id' => $organizadorId,
            'aforo_maximo'   => (int) $data['aforo_maximo'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Retorna un evento por id (incluye el nombre de la categoría).
     */
    public function findById(int $id): ?array
    {
        $sql = 'SELECT e.*, c.nombre AS categoria_nombre
                FROM eventos e
                JOIN categorias c ON c.id = e.categoria_id
                WHERE e.id = :id';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $evento = $stmt->fetch();

        return $evento ?: null;
    }

    /**
     * Lista todos los eventos creados por un organizador específico,
     * incluyendo el estado actual de los cupos (panel de organizador).
     *
     * @param int $organizadorId
     * @param string|null $estado Filtro opcional: activo | cancelado | finalizado
     * @return array
     */
    public function getByOrganizador(int $organizadorId, ?string $estado = null): array
    {
        $sql = 'SELECT
                    e.id,
                    e.titulo,
                    e.descripcion,
                    e.fecha_evento,
                    e.hora_evento,
                    e.lugar,
                    c.nombre AS categoria,
                    e.aforo_maximo,
                    e.aforo_actual,
                    (e.aforo_maximo - e.aforo_actual) AS cupos_disponibles,
                    ROUND((e.aforo_actual / e.aforo_maximo) * 100, 1) AS porcentaje_ocupacion,
                    e.estado,
                    e.created_at,
                    e.updated_at
                FROM eventos e
                JOIN categorias c ON c.id = e.categoria_id
                WHERE e.organizador_id = :organizador_id';

        $params = ['organizador_id' => $organizadorId];

        if ($estado !== null) {
            $sql .= ' AND e.estado = :estado';
            $params['estado'] = $estado;
        }

        $sql .= ' ORDER BY e.fecha_evento ASC, e.hora_evento ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Devuelve un pequeño resumen agregado para el panel del organizador
     * (total de eventos, activos, cupos totales ofertados/ocupados).
     */
    public function getResumenOrganizador(int $organizadorId): array
    {
        $sql = 'SELECT
                    COUNT(*)                                   AS total_eventos,
                    SUM(estado = "activo")                     AS eventos_activos,
                    SUM(estado = "finalizado")                 AS eventos_finalizados,
                    SUM(estado = "cancelado")                  AS eventos_cancelados,
                    COALESCE(SUM(aforo_maximo), 0)              AS cupos_totales_ofertados,
                    COALESCE(SUM(aforo_actual), 0)              AS cupos_totales_ocupados
                FROM eventos
                WHERE organizador_id = :organizador_id';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['organizador_id' => $organizadorId]);

        return $stmt->fetch() ?: [];
    }
}
