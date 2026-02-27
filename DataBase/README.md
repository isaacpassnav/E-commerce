# OKEA DataBase

Modulo de base de datos del e-commerce OKEA.

## Contenido
- `DB - Tablas/base_okea.sql`: esquema principal.
- `DB - Objetos/`: stored procedures, triggers, views y scripts auxiliares.
- `Datos Prueba/`: espacio para seed/manual test data.

## Tablas principales
- Seguridad y acceso:
  - `usuarios`, `roles`, `usuarios_roles`, `permisos`, `roles_permisos`.
- Catalogo:
  - `categorias`, `marcas`, `productos`, `ofertas`.
- Checkout:
  - `carrito`, `pedidos`, `direcciones`, `detalle_pedido`.
- Marketing:
  - `favoritos`, `cupones`, `newsletter`, `cupones_uso`.
- Operacion interna:
  - `pagos`, `logs`, `inventario`, `banners`.

## Stored procedures destacados
- Carrito/Pedidos (`db_carrito_pedidos.sql`):
  - `sp_insertar_carrito`
  - `sp_agregar_producto_carrito`
  - `sp_registrar_pedido`
  - `sp_actualizar_estado_pedido`
  - `sp_reporte_pedidos_periodo`
- Marketing (`db_marketing_y_experiencias_storedprocedures_triggers_views.sql`):
  - `sp_aplicar_cupon_seguro`
  - `sp_reporte_cupones_usados`
  - `sp_limpiar_datos_sensibles`
  - `sp_usuarios_mas_activos`
- Pagos/Inventario (`db_pagos_logs_inventario_banners.sql`):
  - `sp_registrar_pago`
  - `sp_reporte_pagos_periodo`
  - `sp_reporte_pagos_por_estado`
  - `sp_actualizar_inventario`
  - `sp_reporte_stock_bajo`
  - `sp_reporte_ventas_periodo`
  - `sp_top_clientes`
  - `sp_productos_mas_vendidos`
  - `sp_reporte_ventas`
  - `sp_auditoria_general`

## Triggers y views
- Triggers para auditoria de pagos, inventario, banners, cupones, newsletter y favoritos.
- Views para cupones, newsletter, favoritos y engagement de usuarios.

## Orden de carga recomendado
1. `DB - Tablas/base_okea.sql`
2. `DB - Objetos/db_carrito_pedidos.sql`
3. `DB - Objetos/db_marketing_y_experiencias_storedprocedures_triggers_views.sql`
4. `DB - Objetos/db_pagos_logs_inventario_banners.sql`

## Notas operativas
- Revisar compatibilidad de MySQL/MariaDB para `CHECK`, `JSON` y `SIGNAL`.
- Mantener scripts versionados para evitar drift de esquema.
- Ver documentacion general en `../README.md`.
