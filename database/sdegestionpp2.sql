CREATE TABLE `roles` (
  `id_rol` integer PRIMARY KEY AUTO_INCREMENT,
  `nombre_rol` varchar(255) UNIQUE NOT NULL
);

CREATE TABLE `usuarios` (
  `id_usuario` integer PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `apellido` varchar(255) NOT NULL,
  `mail` varchar(255) UNIQUE NOT NULL,
  `contraseña` varchar(255) NOT NULL COMMENT 'almacenada con hash',
  `id_rol` integer NOT NULL
);

CREATE TABLE `categorias` (
  `id_categoria` integer PRIMARY KEY AUTO_INCREMENT,
  `nombre_categoria` varchar(255) UNIQUE NOT NULL
);

CREATE TABLE `productos` (
  `id_producto` integer PRIMARY KEY AUTO_INCREMENT,
  `codigo_interno` varchar(255) UNIQUE,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text,
  `precio` decimal(10,2) NOT NULL,
  `stock_actual` integer NOT NULL,
  `stock_minimo` integer NOT NULL,
  `id_categoria` integer NOT NULL
);

CREATE TABLE `proveedores` (
  `id_proveedor` integer PRIMARY KEY AUTO_INCREMENT,
  `razon_social` varchar(255) NOT NULL,
  `cuit` bigint UNIQUE NOT NULL,
  `telefono` varchar(255),
  `mail` varchar(255),
  `direccion` varchar(255)
);

CREATE TABLE `productos_proveedores` (
  `id_producto` integer NOT NULL,
  `id_proveedor` integer NOT NULL,
  `precio_proveedor` decimal(10,2),
  PRIMARY KEY (`id_producto`, `id_proveedor`)
);

CREATE TABLE `ventas` (
  `id_venta` integer PRIMARY KEY AUTO_INCREMENT,
  `fecha` timestamp NOT NULL,
  `id_usuario` integer NOT NULL,
  `total` decimal(10,2) NOT NULL
);

CREATE TABLE `detalle_ventas` (
  `id_venta` integer NOT NULL,
  `id_producto` integer NOT NULL,
  `cantidad` integer NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_venta`, `id_producto`)
);

CREATE TABLE `solicitudes` (
  `id_solicitud` integer PRIMARY KEY AUTO_INCREMENT,
  `id_producto` integer NOT NULL,
  `id_usuario` integer NOT NULL,
  `fecha` timestamp NOT NULL,
  `atendida` boolean NOT NULL DEFAULT false
);

CREATE TABLE `movimientos_stock` (
  `id_movimiento` integer PRIMARY KEY AUTO_INCREMENT,
  `id_producto` integer NOT NULL,
  `es_entrada` boolean NOT NULL COMMENT 'true = entrada, false = salida',
  `cantidad` integer NOT NULL,
  `motivo` varchar(255),
  `fecha` timestamp NOT NULL,
  `id_usuario` integer NOT NULL
);

CREATE TABLE `estado_orden_compra` (
  `id_estado_orden_compra` integer PRIMARY KEY AUTO_INCREMENT,
  `descripcion` varchar(255) UNIQUE NOT NULL
);

CREATE TABLE `ordenes_compra` (
  `id_orden` integer PRIMARY KEY AUTO_INCREMENT,
  `fecha` timestamp NOT NULL,
  `id_proveedor` integer NOT NULL,
  `id_usuario` integer NOT NULL,
  `id_estado_orden_compra` integer NOT NULL,
  `id_usuario_aprobador` integer,
  `total` decimal(10,2) NOT NULL
);

CREATE TABLE `detalle_ordenes_compra` (
  `id_orden` integer NOT NULL,
  `id_producto` integer NOT NULL,
  `cantidad` integer NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_orden`, `id_producto`)
);

CREATE TABLE `estado_factura` (
  `id_estado_factura` integer PRIMARY KEY AUTO_INCREMENT,
  `descripcion` varchar(255) UNIQUE NOT NULL
);

CREATE TABLE `facturas` (
  `id_factura` integer PRIMARY KEY AUTO_INCREMENT,
  `id_proveedor` integer NOT NULL,
  `id_orden` integer,
  `fecha_emision` timestamp NOT NULL,
  `fecha_vencimiento` timestamp NOT NULL,
  `importe` decimal(10,2) NOT NULL,
  `id_estado_factura` integer NOT NULL
);

CREATE TABLE `pagos` (
  `id_pago` integer PRIMARY KEY AUTO_INCREMENT,
  `id_proveedor` integer NOT NULL,
  `fecha_pago` timestamp NOT NULL,
  `importe_total` decimal(10,2) NOT NULL,
  `id_usuario` integer NOT NULL
);

CREATE TABLE `pago_factura` (
  `id_pago` integer NOT NULL,
  `id_factura` integer NOT NULL,
  `importe_aplicado` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_pago`, `id_factura`)
);

ALTER TABLE `usuarios` ADD FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);

ALTER TABLE `productos` ADD FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`);

ALTER TABLE `productos_proveedores` ADD FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`);

ALTER TABLE `productos_proveedores` ADD FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id_proveedor`);

ALTER TABLE `ventas` ADD FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

ALTER TABLE `detalle_ventas` ADD FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id_venta`);

ALTER TABLE `detalle_ventas` ADD FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`);

ALTER TABLE `solicitudes` ADD FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`);

ALTER TABLE `solicitudes` ADD FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

ALTER TABLE `movimientos_stock` ADD FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`);

ALTER TABLE `movimientos_stock` ADD FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

ALTER TABLE `ordenes_compra` ADD FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id_proveedor`);

ALTER TABLE `ordenes_compra` ADD FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

ALTER TABLE `ordenes_compra` ADD FOREIGN KEY (`id_usuario_aprobador`) REFERENCES `usuarios` (`id_usuario`);

ALTER TABLE `ordenes_compra` ADD FOREIGN KEY (`id_estado_orden_compra`) REFERENCES `estado_orden_compra` (`id_estado_orden_compra`);

ALTER TABLE `detalle_ordenes_compra` ADD FOREIGN KEY (`id_orden`) REFERENCES `ordenes_compra` (`id_orden`);

ALTER TABLE `detalle_ordenes_compra` ADD FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`);

ALTER TABLE `facturas` ADD FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id_proveedor`);

ALTER TABLE `facturas` ADD FOREIGN KEY (`id_orden`) REFERENCES `ordenes_compra` (`id_orden`);

ALTER TABLE `facturas` ADD FOREIGN KEY (`id_estado_factura`) REFERENCES `estado_factura` (`id_estado_factura`);

ALTER TABLE `pagos` ADD FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id_proveedor`);

ALTER TABLE `pagos` ADD FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

ALTER TABLE `pago_factura` ADD FOREIGN KEY (`id_pago`) REFERENCES `pagos` (`id_pago`);

ALTER TABLE `pago_factura` ADD FOREIGN KEY (`id_factura`) REFERENCES `facturas` (`id_factura`);

-- Disable foreign key checks for INSERT
SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO `roles` (`id_rol`, `nombre_rol`)
VALUES
  (1, 'ventas'),
  (2, 'compras'),
  (3, 'administracion'),
  (4, 'gerente');
INSERT INTO `categorias` (`id_categoria`, `nombre_categoria`)
VALUES
  (1, 'general');
INSERT INTO `estado_orden_compra` (`id_estado_orden_compra`, `descripcion`)
VALUES
  (1, 'Pendiente'),
  (2, 'Aprobada'),
  (3, 'Rechazada'),
  (4, 'Recibida');
INSERT INTO `estado_factura` (`id_estado_factura`, `descripcion`)
VALUES
  (1, 'Pendiente'),
  (2, 'Pagada'),
  (3, 'Vencida');

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;
