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
- PDO (MySQL/SQLite)
- Router HTTP propio
- Validaciones y excepciones de dominio

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
