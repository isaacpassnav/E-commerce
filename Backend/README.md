# OKEA Backend

Backend PHP del proyecto OKEA. Actualmente conviven dos capas:
- Legacy: `Routes/`, `Controllers/`, `Modelos/`.
- Core modular: `public/` + `src/`.

## Estructura

```txt
Backend/
|- public/
|  `- index.php
|- src/
|  |- App.php
|  |- routes.php
|  |- Http/
|  |- Services/
|  |- Controllers/
|  |- Database/
|  |- Config/
|  |- Exceptions/
|  `- Support/
|- Routes/
|- Controllers/
|- Modelos/
`- avance_backend/
```

## Stack
- PHP 7.4+
- PDO (MySQL/PostgreSQL/SQLite)
- Router HTTP propio
- Validaciones y excepciones de dominio

## Requisitos PHP
- Para MySQL: `pdo_mysql`
- Para Supabase/PostgreSQL: `pdo_pgsql`

En Laragon, habilita extensiones en `php.ini`:
- `extension=pdo_pgsql`
- `extension=pgsql`

## Configuracion
1. Copiar `.env.example` a `.env`.
2. Ajustar:
   - `DB_DRIVER`
   - `DB_HOST`
   - `DB_PORT`
   - `DB_DATABASE`
   - `DB_USERNAME`
   - `DB_PASSWORD`
3. En Laragon, usar docroot `Backend/public`.

### Conexion a Supabase (Postgres)
- Usa `DB_DRIVER=pgsql`.
- Usa host/puerto de pooler (`aws-...pooler.supabase.com:6543`) para conexiones de app.
- Configura `DB_SSLMODE=require`.
- Si usas `DB_URL`, encodea el password cuando tenga caracteres especiales (`@` => `%40`).

Ejemplo rapido:
```env
DB_DRIVER=pgsql
DB_HOST=aws-1-eu-west-1.pooler.supabase.com
DB_PORT=6543
DB_DATABASE=postgres
DB_USERNAME=postgres.<project-ref>
DB_PASSWORD=<db-password>
DB_SSLMODE=require
```

## Endpoints core
- `GET /api/health`
- `GET|POST|PUT|DELETE /api/coupons...`
- `GET|POST|PUT|DELETE /api/newsletter...`
- `GET|POST|DELETE /api/favorites...`
- `POST /api/carts/{userId}`
- `GET /api/carts/{cartId}`
- `POST /api/carts/{cartId}/items`
- `POST /api/addresses`
- `POST /api/carts/{cartId}/checkout`
- `POST /api/orders/{orderId}/confirm`

## Nota
- La API legacy sigue disponible por compatibilidad historica.
- Recomendado: usar la capa `public + src` para nuevas integraciones.
- Documentacion general en `../README.md`.
