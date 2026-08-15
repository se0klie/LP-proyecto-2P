<?php

require_once __DIR__ . '/../config/Database.php';

class Evento
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function categoriaExiste(int $categoriaId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM categorias WHERE id = :id');
        $stmt->execute(['id' => $categoriaId]);
        return (bool) $stmt->fetchColumn();
    }

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

    public function update(
        int $eventoId,
        array $data,
        int $organizadorId
    ): bool {
        $sql = "
            UPDATE eventos
            SET
                titulo = :titulo,
                descripcion = :descripcion,
                fecha_evento = :fecha_evento,
                hora_evento = :hora_evento,
                lugar = :lugar,
                categoria_id = :categoria_id,
                aforo_maximo = :aforo_maximo
            WHERE id = :id
            AND organizador_id = :organizador_id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':titulo' => $data['titulo'],
            ':descripcion' => $data['descripcion'],
            ':fecha_evento' => $data['fecha_evento'],
            ':hora_evento' => $data['hora_evento'] ?? null,
            ':lugar' => $data['lugar'] ?? null,
            ':categoria_id' => (int) $data['categoria_id'],
            ':aforo_maximo' => (int) $data['aforo_maximo'],
            ':id' => $eventoId,
            ':organizador_id' => $organizadorId
        ]);
    }
    public function delete(
        int $eventoId,
        int $organizadorId
    ): bool {
        $sql = "
            DELETE FROM eventos
            WHERE id = :id
            AND organizador_id = :organizador_id
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id' => $eventoId,
            ':organizador_id' => $organizadorId
        ]);

        return $stmt->rowCount() > 0;
    }
}
