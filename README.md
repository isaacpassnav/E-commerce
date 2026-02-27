# OKEA E-commerce Monorepo

Proyecto fullstack de e-commerce con frontend React, backend PHP y base de datos MySQL para catalogo, carrito, pedidos, marketing, pagos, inventario y analitica.

## Estado Actual Del Repositorio

### Ultimos cambios detectados
- `8daa0be` (2026-02-25): cambios por mayusculas/minusculas y despliegue en Vercel.
- `9ba4157` (2026-02-24): correcciones backend y ejecucion funcional.
- `958f275` (2026-02-24): refactor, resolucion de conflictos y depuracion backend.

### Situacion tecnica
- Monorepo con tres bloques principales:
  - `web-proyect/` (frontend React + Vite).
  - `Backend/` (backend con capa legacy y capa modular nueva).
  - `DataBase/` (modelo SQL, SPs, triggers, views y scripts de soporte).

## Stack Tecnologico

### Frontend (`web-proyect`)
- React `19.1.1`
- React DOM `19.1.1`
- React Router DOM `7.9.3`
- Vite `7.1.2`
- Tailwind CSS `4.1.12`
- `@tailwindcss/vite` `4.1.12`
- `@fluentui/react-icons` `2.0.316`
- `react-icons` `5.5.0`
- ESLint 9

### Backend (`Backend`)
- PHP `7.4+`.
- Arquitectura hibrida:
  - Capa legacy: `Backend/Routes`, `Backend/Controllers`, `Backend/Modelos`.
  - Capa modular nueva: `Backend/src` + `Backend/public/index.php`.
- Acceso a datos con PDO (MySQL o SQLite por configuracion).

### Base de datos (`DataBase`)
- MySQL/MariaDB.
- Script maestro: `DataBase/DB - Tablas/base_okea.sql`.
- Objetos SQL avanzados en `DataBase/DB - Objetos`.

## Arquitectura Y Separacion De Capas

### Front
- Home comercial (presentacion, categorias, ofertas, recomendados, marcas, FAQ).
- Catalogo por categorias.
- Detalle de producto por slug.
- Carrito y checkout.
- Login y perfil (mock).
- Favoritos.

### Back
- Legacy API (`Backend/Routes/api.php`):
  - Endpoints de `coupons`, `newsletter`, `favorites`.
  - Logica REST simulada con datos en memoria.
- Core API modular (`Backend/src`):
  - Servicios para cupones, newsletter, favoritos, carrito y pedidos.
  - Validaciones centralizadas.
  - Manejo de errores HTTP consistente.
  - API key opcional por `.env`.

### DB
- Modelo relacional para usuarios, catalogo, checkout, marketing y operacion.
- Stored procedures para flujos criticos.
- Triggers para auditoria/consistencia.
- Views para analitica.

## Historias De Usuario (MVP + Escalamiento)

### Cliente final
- `US-01` Como visitante quiero explorar categorias para encontrar productos facil.
- `US-02` Como cliente quiero ver detalle del producto para decidir compra.
- `US-03` Como cliente quiero gestionar carrito (agregar, quitar, editar cantidad).
- `US-04` Como cliente quiero registrar direccion para completar checkout.
- `US-05` Como cliente quiero generar pedido desde carrito.
- `US-06` Como cliente quiero aplicar cupones validos.
- `US-07` Como cliente quiero guardar favoritos.
- `US-08` Como cliente quiero suscribirme a newsletter.

### Operacion/negocio
- `US-09` Como negocio quiero confirmar pedidos pagados.
- `US-10` Como negocio quiero reportes de pagos y pedidos por periodo.
- `US-11` Como negocio quiero control de inventario y stock.
- `US-12` Como negocio quiero logs para auditoria y soporte.

### Escalamiento tecnico
- `US-13` Como equipo quiero separar capas para crecer sin deuda.
- `US-14` Como equipo quiero estandarizar API para apps externas.
- `US-15` Como equipo quiero CI con tests automaticos.

## Features Checklist

### Frontend
- [x] Home + bloques comerciales.
- [x] Catalogo por categorias.
- [x] Detalle de producto por slug.
- [x] Carrito UI.
- [x] Login/perfil mock.
- [x] Favoritos UI.
- [ ] Integracion real front-back.
- [ ] Persistencia real de sesion/auth.

### Backend
- [x] Endpoints legacy de marketing.
- [x] Servicios backend modulares (marketing + checkout).
- [x] Config DB por `.env`.
- [x] Manejo de errores HTTP.
- [ ] Suite de tests automatizada (formal).
- [ ] Versionado de API (`/api/v1`).
- [ ] Auth real (JWT o sesion server-side).

### DataBase
- [x] Esquema principal.
- [x] SPs para operaciones clave.
- [x] Triggers de auditoria/consistencia.
- [x] Views analiticas.
- [ ] Migraciones automatizadas.
- [ ] Seeds oficiales de desarrollo.

## Logica De Negocio (Resumen)

### Cupones
- Alta con validacion de tipo, fechas, limites y duplicidad.
- Validacion por vigencia, estado, limites de uso y monto minimo.

### Newsletter
- Alta por email unico.
- Gestion de estado (`activo`, `inactivo`, `bloqueado`) y baja con motivo.

### Favoritos
- Registro por usuario-producto.
- Prevencion de duplicados por restriccion unica.

### Checkout
- Creacion/recuperacion de carrito activo.
- Alta de items con control de stock.
- Generacion de pedido en transaccion:
  - inserta pedido y detalle,
  - descuenta stock,
  - finaliza carrito y limpia detalle.

## Estructura Del Repositorio

```txt
.
|- Backend/
|  |- public/
|  |- src/
|  |- Routes/
|  |- Controllers/
|  |- Modelos/
|  `- avance_backend/
|- DataBase/
|  |- DB - Tablas/
|  |- DB - Objetos/
|  `- Datos Prueba/
`- web-proyect/
   |- src/
   |- package.json
   `- README.md
```

## Configuracion Local

### Base de datos
1. Importar `DataBase/DB - Tablas/base_okea.sql`.
2. Importar objetos necesarios desde `DataBase/DB - Objetos/`.

### Backend (Laragon)
1. Copiar `Backend/.env.example` a `Backend/.env`.
2. Configurar credenciales DB.
3. En Laragon, apuntar el virtual host a `Backend/public`.
4. Probar `GET /api/health`.

### Frontend
1. Entrar a `web-proyect`.
2. Ejecutar `npm install`.
3. Ejecutar `npm run dev`.

## Endpoints API (Core Modular)

### Health
- `GET /api/health`

### Cupones
- `GET /api/coupons`
- `POST /api/coupons`
- `GET /api/coupons/{id}`
- `PUT /api/coupons/{id}`
- `DELETE /api/coupons/{id}`
- `GET /api/coupons/{id}/validar?monto_pedido=...`

### Newsletter
- `GET /api/newsletter`
- `POST /api/newsletter`
- `PUT /api/newsletter/{id}`
- `DELETE /api/newsletter/{id}`

### Favoritos
- `GET /api/favorites?id_usuario=...`
- `POST /api/favorites`
- `DELETE /api/favorites/{id}`

### Carrito/Pedido
- `POST /api/carts/{userId}`
- `GET /api/carts/{cartId}`
- `POST /api/carts/{cartId}/items`
- `POST /api/addresses`
- `POST /api/carts/{cartId}/checkout`
- `POST /api/orders/{orderId}/confirm`

## Escalabilidad Y Robustez (Siguiente etapa)

- Versionar API (`/api/v1`).
- Extraer repositorios por modulo.
- Implementar auth real.
- Anadir tests por capa (servicio + integracion).
- Definir migraciones y seeds reproducibles.
- Agregar observabilidad (logs estructurados, metricas y trazas).
