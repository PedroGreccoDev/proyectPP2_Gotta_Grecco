-- ==========================================================
-- Sprint 2 - ABM de Proveedores: cambios en la base de datos
-- Ejecutar UNA sola vez en phpMyAdmin (pestaña SQL) sobre la
-- base sdegestionpp2.
-- ==========================================================
SET NAMES utf8mb4;

-- ----------------------------------------------------------
-- HU-09: baja lógica de proveedores
-- Los proveedores existentes quedan activos (DEFAULT TRUE)
-- ----------------------------------------------------------
ALTER TABLE `proveedores`
  ADD COLUMN `activo` boolean NOT NULL DEFAULT TRUE;

-- ----------------------------------------------------------
-- HU-11: auditoría de cambios
-- accion_auditoria es un catálogo (mismo criterio que
-- estado_orden_compra y estado_factura): evita texto libre.
-- ----------------------------------------------------------
CREATE TABLE `accion_auditoria` (
  `id_accion` integer PRIMARY KEY AUTO_INCREMENT,
  `descripcion` varchar(255) UNIQUE NOT NULL
);

INSERT INTO `accion_auditoria` (`id_accion`, `descripcion`)
VALUES
  (1, 'Alta'),
  (2, 'Modificación'),
  (3, 'Baja lógica'),
  (4, 'Reactivación');

CREATE TABLE `auditoria` (
  `id_auditoria` integer PRIMARY KEY AUTO_INCREMENT,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_usuario` integer NOT NULL,
  `entidad` varchar(50) NOT NULL,
  `id_registro` integer NOT NULL,
  `id_accion` integer NOT NULL,
  `campo` varchar(50),
  `valor_anterior` text,
  `valor_nuevo` text
);

ALTER TABLE `auditoria` ADD FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

ALTER TABLE `auditoria` ADD FOREIGN KEY (`id_accion`) REFERENCES `accion_auditoria` (`id_accion`);
