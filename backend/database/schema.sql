-- ============================================================
-- ESPOL Eventos - Esquema de Base de Datos
-- Módulo: Creación y Administración de Eventos (Aforo)
-- Responsable: Hailie Jimenez
-- ============================================================

CREATE DATABASE IF NOT EXISTS espol_eventos
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE espol_eventos;

-- ------------------------------------------------------------
-- Tabla: usuarios
-- Nota: la autenticación completa la maneja otro módulo,
-- pero se define aquí la estructura mínima requerida para
-- relacionar un evento con su organizador.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(150)        NOT NULL,
    email           VARCHAR(150)        NOT NULL UNIQUE,
    password_hash   VARCHAR(255)        NOT NULL,
    rol             ENUM('estudiante','organizador','admin') NOT NULL DEFAULT 'estudiante',
    carrera         VARCHAR(150)        NULL,
    created_at      TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP
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
-- Tabla: eventos
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

-- ------------------------------------------------------------
-- Usuario organizador de prueba (password: "Organizador123")
-- Hash generado con password_hash() - bcrypt
-- ------------------------------------------------------------
-- INSERT INTO usuarios (nombre, email, password_hash, rol)
-- VALUES ('Organizador Demo', 'organizador@espol.edu.ec',
--         '$2y$10$examplehashreplaceinreal', 'organizador');
