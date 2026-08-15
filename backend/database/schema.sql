-- ============================================================
-- ESPOL Eventos - Esquema de Base de Datos
-- Módulo: Creación y Administración de Eventos (Aforo)
-- Responsable: Hailie Jimenez
-- ============================================================

CREATE DATABASE IF NOT EXISTS espol_eventos
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE espol_eventos;

-- ------------------------------------------------------------
-- Tabla: usuarios HAILIE JIMENEZ
-- Nota: la autenticación completa la maneja otro módulo,
-- pero se define aquí la estructura mínima requerida para
-- relacionar un evento con su organizador.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario         VARCHAR(100) NOT NULL UNIQUE,
    correo          VARCHAR(150) NOT NULL UNIQUE,
    contrasena      VARCHAR(255) NOT NULL,
    cargo           ENUM('estudiante', 'administrativo', 'profesor') NOT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
-- ------------------------------------------------------------
-- Tabla: categorias
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categorias (
    id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre  VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

INSERT INTO categorias (nombre) VALUES
    ('Académico'),
    ('Deportivo'),
    ('Cultural'),
    ('Tecnológico'),
    ('Voluntariado'),
    ('Bienestar Estudiantil')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- ------------------------------------------------------------
-- Tabla: eventos HAILIE JIMENEZ
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS eventos (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo          VARCHAR(200)        NOT NULL,
    descripcion     TEXT                NOT NULL,
    fecha_evento    DATE                NOT NULL,
    hora_evento     TIME                NULL,
    lugar           VARCHAR(200)        NULL,
    categoria_id    INT UNSIGNED        NOT NULL,
    organizador_id  INT UNSIGNED        NOT NULL,
    aforo_maximo    INT UNSIGNED        NOT NULL,
    aforo_actual    INT UNSIGNED        NOT NULL DEFAULT 0,
    estado          ENUM('activo','cancelado','finalizado') NOT NULL DEFAULT 'activo',
    created_at      TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP
                                         ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_eventos_categoria
        FOREIGN KEY (categoria_id) REFERENCES categorias(id),
    CONSTRAINT fk_eventos_organizador
        FOREIGN KEY (organizador_id) REFERENCES usuarios(id),
    CONSTRAINT chk_aforo_actual_no_excede
        CHECK (aforo_actual <= aforo_maximo)
) ENGINE=InnoDB;

CREATE INDEX idx_eventos_organizador ON eventos(organizador_id);
CREATE INDEX idx_eventos_fecha ON eventos(fecha_evento);
CREATE INDEX idx_eventos_estado ON eventos(estado);


CREATE TABLE IF NOT EXISTS participantes_evento (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    evento_id       INT UNSIGNED NOT NULL,
    usuario_id      INT UNSIGNED NOT NULL,
    fecha_registro  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_participantes_evento
        FOREIGN KEY (evento_id) REFERENCES eventos(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_participantes_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE,

    UNIQUE KEY uk_evento_usuario (evento_id, usuario_id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabla: resenas (Módulo de Christian Macias)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS resenas (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    evento_id       INT UNSIGNED NOT NULL,
    estudiante_id   INT UNSIGNED NOT NULL,
    calificacion    TINYINT UNSIGNED NOT NULL CHECK (calificacion BETWEEN 1 AND 5),
    comentario      TEXT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_resenas_evento FOREIGN KEY (evento_id) REFERENCES eventos(id),
    CONSTRAINT fk_resenas_estudiante FOREIGN KEY (estudiante_id) REFERENCES usuarios(id),
    UNIQUE KEY uk_evento_estudiante (evento_id, estudiante_id)
) ENGINE=InnoDB;


INSERT INTO usuarios (id, usuario, correo, contrasena, cargo)
VALUES (
    1,
    'organizador',
    'organizador@espol.edu.ec',
    '$2y$10$example',
    'administrativo'
)
ON DUPLICATE KEY UPDATE id = id;

INSERT INTO usuarios (id, usuario, correo, contrasena, cargo)
VALUES (
    2,
    'estudiante',
    'estudiante@espol.edu.ec',
    '$2y$10$example',
    'estudiante'
)
ON DUPLICATE KEY UPDATE id = id;