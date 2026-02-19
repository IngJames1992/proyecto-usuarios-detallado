-- =====================================================
-- SCHEMA BASE DE DATOS: Sistema de Usuarios
-- Actividad 2 - Patrones de Diseño
-- =====================================================

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS sistema_usuarios
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE sistema_usuarios;

-- =====================================================
-- TABLA: usuarios
-- =====================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    tipo_usuario ENUM('admin', 'normal') DEFAULT 'normal',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    
    -- Índices para mejorar rendimiento
    INDEX idx_email (email),
    INDEX idx_tipo (tipo_usuario),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: logs_notificaciones
-- Para demostrar el patrón Observer
-- =====================================================
CREATE TABLE IF NOT EXISTS logs_notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo_notificacion ENUM('email', 'sms', 'push') NOT NULL,
    mensaje TEXT NOT NULL,
    enviado BOOLEAN DEFAULT FALSE,
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id),
    INDEX idx_tipo (tipo_notificacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DATOS DE PRUEBA
-- =====================================================

-- Insertar usuarios de ejemplo
INSERT INTO usuarios (nombre, email, tipo_usuario) VALUES
('Administrador Sistema', 'admin@sistema.com', 'admin'),
('Juan Pérez', 'juan.perez@email.com', 'normal'),
('María García', 'maria.garcia@email.com', 'normal'),
('Carlos López', 'carlos.lopez@email.com', 'admin'),
('Ana Martínez', 'ana.martinez@email.com', 'normal');

-- Insertar algunos logs de notificaciones
INSERT INTO logs_notificaciones (usuario_id, tipo_notificacion, mensaje, enviado) VALUES
(1, 'email', 'Bienvenido al sistema', TRUE),
(2, 'email', 'Tu cuenta ha sido creada', TRUE),
(2, 'sms', 'Código de verificación: 1234', TRUE),
(3, 'push', 'Tienes un nuevo mensaje', TRUE),
(4, 'email', 'Bienvenido Administrador', TRUE);

-- =====================================================
-- VERIFICACIÓN
-- =====================================================

-- Ver usuarios creados
SELECT * FROM usuarios;

-- Ver logs de notificaciones
SELECT 
    l.id,
    u.nombre,
    l.tipo_notificacion,
    l.mensaje,
    l.enviado,
    l.fecha_envio
FROM logs_notificaciones l
JOIN usuarios u ON l.usuario_id = u.id
ORDER BY l.fecha_envio DESC;
