<?php

require_once __DIR__ . '/../config/Database.php';

/**
 * Clase Inscripcion
 * Módulo: Inscripción a eventos y emisión de pase digital
 * Responsable: Paulo Tapia
 */
class Inscripcion
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Inscribe a un estudiante en un evento:
     *   1. Bloquea la fila del evento (FOR UPDATE) para evitar condiciones
     *      de carrera cuando varios estudiantes se inscriben a la vez.
     *   2. Verifica que el evento exista, esté activo y tenga cupo.
     *   3. Verifica que el estudiante no esté ya inscrito.
     *   4. Inserta la inscripción, genera el código del pase digital
     *      y descuenta un cupo del aforo.
     *
     * @throws RuntimeException con un código de error identificable
     *         (EVENTO_NO_EXISTE | EVENTO_NO_ACTIVO | CUPOS_AGOTADOS | YA_INSCRITO)
     */
    public function inscribir(int $eventoId, int $estudianteId): array
    {
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare(
                'SELECT id, titulo, fecha_evento, hora_evento, lugar,
                        aforo_maximo, aforo_actual, estado
                 FROM eventos
                 WHERE id = :id
                 FOR UPDATE'
            );
            $stmt->execute(['id' => $eventoId]);
            $evento = $stmt->fetch();

            if (!$evento) {
                throw new RuntimeException('EVENTO_NO_EXISTE');
            }

            if ($evento['estado'] !== 'activo') {
                throw new RuntimeException('EVENTO_NO_ACTIVO');
            }

            if ((int) $evento['aforo_actual'] >= (int) $evento['aforo_maximo']) {
                throw new RuntimeException('CUPOS_AGOTADOS');
            }

            $stmtCheck = $this->db->prepare(
                'SELECT id FROM inscripciones WHERE evento_id = :evento_id AND estudiante_id = :estudiante_id'
            );
            $stmtCheck->execute([
                'evento_id'     => $eventoId,
                'estudiante_id' => $estudianteId,
            ]);
            
            if ($stmtCheck->fetch()) {
                throw new RuntimeException('YA_INSCRITO');
            }

            $codigoPase = $this->generarCodigoPase($eventoId);

            $stmtInsert = $this->db->prepare(
                'INSERT INTO inscripciones (evento_id, estudiante_id, codigo_pase, estado)
                 VALUES (:evento_id, :estudiante_id, :codigo_pase, "valido")'
            );
            $stmtInsert->execute([
                'evento_id'     => $eventoId,
                'estudiante_id' => $estudianteId,
                'codigo_pase'   => $codigoPase,
            ]);
            $inscripcionId = (int) $this->db->lastInsertId();

            $stmtUpdate = $this->db->prepare(
                'UPDATE eventos SET aforo_actual = aforo_actual + 1 WHERE id = :id'
            );
            $stmtUpdate->execute(['id' => $eventoId]);

            $this->db->commit();
            return [
                'inscripcion_id' => $inscripcionId,
                'codigo_pase'    => $codigoPase,
                'estado'         => 'valido',
                'evento'         => [
                    'id'           => (int) $evento['id'],
                    'titulo'       => $evento['titulo'],
                    'fecha_evento' => $evento['fecha_evento'],
                    'hora_evento'  => $evento['hora_evento'],
                    'lugar'        => $evento['lugar'],
                ],
            ];
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /** Genera un código único de pase digital, p. ej. EVT-2026-04821 */
    private function generarCodigoPase(int $eventoId): string
    {
        $anio = date('Y');
        do {
            $codigo = sprintf('EVT-%s-%05d', $anio, random_int(0, 99999));
            $stmt = $this->db->prepare('SELECT 1 FROM inscripciones WHERE codigo_pase = :codigo');
            $stmt->execute(['codigo' => $codigo]);
        } while ($stmt->fetchColumn());

        return $codigo;
    }

    /** Devuelve las inscripciones (con datos del evento) de un estudiante. */
    public function getByEstudiante(int $estudianteId): array
    {
        $sql = 'SELECT
                    i.id AS inscripcion_id,
                    i.codigo_pase,
                    i.estado,
                    i.fecha_inscripcion,
                    e.id AS evento_id,
                    e.titulo,
                    e.fecha_evento,
                    e.hora_evento,
                    e.lugar
                FROM inscripciones i
                JOIN eventos e ON e.id = i.evento_id
                WHERE i.estudiante_id = :estudiante_id
                ORDER BY e.fecha_evento ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['estudiante_id' => $estudianteId]);

        return $stmt->fetchAll();
    }
}

