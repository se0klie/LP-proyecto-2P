-- ============================================================
-- ESPOL Eventos - Migración adicional
-- Módulo: Exploración de catálogo e Inscripción a eventos
-- Responsable: Paulo Tapia
--
-- Ejecutar DESPUÉS de backend/database/schema.sql, ya que
-- depende de las tablas `eventos` y `usuarios` creadas ahí.
-- No modifica ni reemplaza ninguna tabla existente.
-- ============================================================

USE espol_eventos;

CREATE TABLE IF NOT EXISTS inscripciones (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    evento_id           INT UNSIGNED        NOT NULL,
    estudiante_id       INT UNSIGNED        NOT NULL,
    codigo_pase         VARCHAR(30)         NOT NULL UNIQUE,
    estado              ENUM('valido','cancelado') NOT NULL DEFAULT 'valido',
    fecha_inscripcion   TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_inscripciones_evento
        FOREIGN KEY (evento_id) REFERENCES eventos(id),
    CONSTRAINT fk_inscripciones_estudiante
        FOREIGN KEY (estudiante_id) REFERENCES usuarios(id),

    -- Un mismo estudiante no puede inscribirse dos veces al mismo evento
    UNIQUE KEY uk_evento_estudiante_inscripcion (evento_id, estudiante_id)
) ENGINE=InnoDB;

CREATE INDEX idx_inscripciones_evento ON inscripciones(evento_id);
CREATE INDEX idx_inscripciones_estudiante ON inscripciones(estudiante_id);
