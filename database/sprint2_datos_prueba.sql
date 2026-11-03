-- ==========================================================
-- OPCIONAL - Datos de prueba para el ABM de proveedores
-- Ejecutar DESPUÉS de sprint2_proveedores.sql y con al menos
-- un usuario registrado (las órdenes de compra necesitan uno).
--
-- Los CUIT son válidos según el dígito verificador (HU-10).
-- Ojo: tres de los CUIT que aparecen en los mockups del PDF
-- (30-70987654-3, 30-71555888-2 y 30-69874521-0) NO pasan esa
-- validación; acá van con el dígito corregido.
-- ==========================================================
SET NAMES utf8mb4;

INSERT INTO `proveedores` (`razon_social`, `cuit`, `telefono`, `mail`, `direccion`, `activo`)
VALUES
  ('Distribuidora El Sol SRL', 30712345671, '0353-4561234', 'ventas@elsol.com.ar', 'Av. San Martín 450, Río Tercero', 1),
  ('Lácteos del Centro SA', 30709876542, '0353-4220011', 'compras@lacteoscentro.com.ar', 'Ruta 9 km 712, Río Tercero', 1),
  ('Bebidas Córdoba SRL', 30715558889, '0351-4887766', 'pedidos@bebidascba.com.ar', 'Bv. Illia 320, Córdoba', 1),
  ('Limpieza Total SA', 30698745211, '0353-4119900', 'info@limpiezatotal.com.ar', 'Belgrano 88, Río Tercero', 1),
  ('Panificadora La Espiga', 20284561237, '0353-4623344', NULL, NULL, 0);

-- Bebidas Córdoba SRL: 1 orden de compra pendiente y 2 facturas sin pagar
-- (sirve para probar el bloqueo de la baja, HU-09 escenario 2)
INSERT INTO `ordenes_compra` (`fecha`, `id_proveedor`, `id_usuario`, `id_estado_orden_compra`, `total`)
VALUES
  (NOW(),
   (SELECT `id_proveedor` FROM `proveedores` WHERE `cuit` = 30715558889),
   (SELECT MIN(`id_usuario`) FROM `usuarios`),
   1, 15000.00);

INSERT INTO `facturas` (`id_proveedor`, `fecha_emision`, `fecha_vencimiento`, `importe`, `id_estado_factura`)
VALUES
  ((SELECT `id_proveedor` FROM `proveedores` WHERE `cuit` = 30715558889), NOW(), DATE_ADD(NOW(), INTERVAL 15 DAY), 8000.00, 1),
  ((SELECT `id_proveedor` FROM `proveedores` WHERE `cuit` = 30715558889), NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 7000.00, 1);
