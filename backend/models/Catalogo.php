<?php

require_once __DIR__ . '/../config/Database.php';

/**
 * Clase Catalogo
 * Módulo: Exploración de catálogo de eventos
 * Responsable: Paulo Tapia
 *
 * Solo realiza lecturas (SELECT) sobre las tablas `eventos` y
 * `categorias`. No modifica el modelo Evento.php de Hailie para
 * no interferir con su módulo de creación/administración.
 */
class Catalogo
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Lista eventos activos, con filtros opcionales de búsqueda y categoría.
     *
     * @param string|null $busqueda   Texto libre (busca en título y lugar)
     * @param int|null    $categoriaId Filtro por categoría
     * @param string      $estado     Estado del evento a listar (por defecto "activo")
     * @return array
     */
    public function listar(?string $busqueda = null, ?int $categoriaId = null, string $estado = 'activo'): array
    {
        $sql = 'SELECT
                    e.id,
                    e.titulo,
                    e.descripcion,
                    e.fecha_evento,
                    e.hora_evento,
                    e.lugar,
                    c.id AS categoria_id,
                    c.nombre AS categoria,
                    e.aforo_maximo,
                    e.aforo_actual,
                    (e.aforo_maximo - e.aforo_actual) AS cupos_disponibles,
                    e.estado
                FROM eventos e
                JOIN categorias c ON c.id = e.categoria_id
                WHERE e.estado = :estado';

        $params = ['estado' => $estado];

        if (!empty($busqueda)) {
            $sql .= ' AND (e.titulo LIKE :busqueda OR e.lugar LIKE :busqueda)';
            $params['busqueda'] = '%' . trim($busqueda) . '%';
        }

        if (!empty($categoriaId)) {
            $sql .= ' AND e.categoria_id = :categoria_id';
            $params['categoria_id'] = $categoriaId;
        }

        $sql .= ' ORDER BY e.fecha_evento ASC, e.hora_evento ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Retorna el detalle de un evento puntual para la vista de exploración,
     * incluyendo cupos disponibles.
     */
    public function detalle(int $eventoId): ?array
    {
        $sql = 'SELECT
                    e.id,
                    e.titulo,
                    e.descripcion,
                    e.fecha_evento,
                    e.hora_evento,
                    e.lugar,
                    c.id AS categoria_id,
                    c.nombre AS categoria,
                    e.aforo_maximo,
                    e.aforo_actual,
                    (e.aforo_maximo - e.aforo_actual) AS cupos_disponibles,
                    e.estado
                FROM eventos e
                JOIN categorias c ON c.id = e.categoria_id
                WHERE e.id = :id';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $eventoId]);
        $evento = $stmt->fetch();

        return $evento ?: null;
    }

    /** Lista simple de categorías, para poblar el filtro del catálogo. */
    public function listarCategorias(): array
    {
        $stmt = $this->db->query('SELECT id, nombre FROM categorias ORDER BY nombre ASC');
        return $stmt->fetchAll();
    }
}
