# REQUERIMIENTOS.md — HiloBlanco Backend API

## 1. Descripción del Proyecto

**HiloBlanco** es la API REST del sistema de gestión para una boutique especializada en vestidos de novia. Proporciona el backend para dos consumidores principales:

- **Panel administrativo React SPA**: gestión interna del catálogo, usuarios y configuración del sitio.
- **Chatbot externo** (WhatsApp / web): consulta pública del catálogo y datos de contacto.

**Stack**: Laravel 11, PHP 8.2+, MySQL 8.0+, Laravel Sanctum v4, Spatie Laravel Permission v6.

---

## 2. Levantamiento Funcional

### 2.1 Gestión del Catálogo

- Crear y administrar **categorías/colecciones** de vestidos (Clásica, Moderna, Bohemia, Alta Costura).
- Crear y administrar **productos** con:
  - Múltiples imágenes ordenables (una marcada como portada `is_cover`).
  - Atributos variables en JSON (`details`): tallas, telas, colores, tiempo de confección.
  - Precio referencial opcional (puede ocultarse al público vía setting `show_prices`).
  - Marcas de destacado (`is_featured`) y personalizable (`is_customizable`).
- **Soft-delete vía columna `status`** (1 = activo, 0 = inactivo): los registros nunca se eliminan físicamente, preservando integridad referencial e historial.
- Filtros en el catálogo público: categoría, destacados, búsqueda por nombre.

### 2.2 Autenticación y Acceso

- Login con email/password → token Bearer Sanctum.
- **Tokens SPA**: duración 8 horas, para el panel React.
- **Tokens de chatbot**: sin expiración, con `abilities` limitadas (`products:read`, `settings:read`), creados y revocados por el `super_admin`.
- El `super_admin` puede listar y revocar todos los tokens de API.

### 2.3 Gestión de Usuarios

- Solo el `super_admin` puede crear, listar, actualizar y desactivar usuarios.
- Roles disponibles: `super_admin` (control total) y `admin` (operaciones de catálogo).
- Toggle de status (`PATCH /users/{id}/toggle-status`).

### 2.4 Configuración del Sistema

- Key-value store en tabla `settings` para ajustes del sitio.
- Settings marcados como `is_public = 1` son accesibles sin autenticación (para el chatbot).
- El `super_admin` actualiza settings individualmente o en lote (transacción).
- Tipos de value soportados: `string`, `boolean`, `integer`, `json`.

---

## 3. Módulos

### Módulos actuales

| Módulo | Descripción |
|--------|-------------|
| `Auth` | Login, logout, datos del usuario autenticado |
| `Tokens` | Gestión de tokens de API para integraciones externas |
| `Categories` | CRUD de colecciones/categorías de vestidos |
| `Products` | CRUD completo del catálogo + gestión de imágenes |
| `Users` | Gestión de usuarios del sistema (solo `super_admin`) |
| `Settings` | Configuración general del sitio (key-value) |

### Módulos futuros planificados (estructura reservada)

| Módulo | Descripción |
|--------|-------------|
| `Appointments` | Citas con clientas |
| `Clients` | Registro de clientas |
| `Orders` | Pedidos personalizados |
| `Gallery` | Galería editorial / lookbook |

---

## 4. Entidades del Modelo de Datos

### `users`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BIGINT UNSIGNED PK | |
| name | VARCHAR(100) NOT NULL | Nombre completo |
| email | VARCHAR(150) UNIQUE NOT NULL | Correo electrónico |
| email_verified_at | TIMESTAMP NULL | |
| password | VARCHAR(255) NOT NULL | Hash bcrypt |
| status | TINYINT(1) DEFAULT 1 | 1=activo, 0=inactivo |
| remember_token | VARCHAR(100) NULL | |
| created_at / updated_at | TIMESTAMP | |

### `categories`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BIGINT UNSIGNED PK | |
| name | VARCHAR(100) NOT NULL | Nombre de la colección |
| slug | VARCHAR(120) UNIQUE NOT NULL | Identificador URL |
| description | TEXT NULL | Descripción |
| image | VARCHAR(255) NULL | Imagen representativa (path) |
| status | TINYINT(1) DEFAULT 1 | 1=activa, 0=inactiva |
| sort_order | SMALLINT DEFAULT 0 | Orden visual |
| created_at / updated_at | TIMESTAMP | |

### `products`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BIGINT UNSIGNED PK | |
| category_id | BIGINT UNSIGNED FK | → categories.id ON DELETE RESTRICT |
| sku | VARCHAR(60) UNIQUE NOT NULL | Código interno |
| name | VARCHAR(200) NOT NULL | Nombre del vestido |
| slug | VARCHAR(220) UNIQUE NOT NULL | Identificador URL |
| description | TEXT NULL | Descripción |
| details | JSON NULL | Atributos: tallas, telas, colores, tiempo_confeccion |
| price | DECIMAL(10,2) NULL | Precio referencia (NULL = a consultar) |
| is_featured | TINYINT(1) DEFAULT 0 | Destacado en catálogo |
| is_customizable | TINYINT(1) DEFAULT 0 | Acepta personalización |
| status | TINYINT(1) DEFAULT 1 | 1=activo, 0=inactivo |
| sort_order | SMALLINT DEFAULT 0 | Orden visual |
| created_at / updated_at | TIMESTAMP | |

### `product_images`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BIGINT UNSIGNED PK | |
| product_id | BIGINT UNSIGNED FK | → products.id ON DELETE CASCADE |
| path | VARCHAR(255) NOT NULL | Ruta en storage/app/public |
| alt_text | VARCHAR(150) NULL | Texto alternativo (SEO/accesibilidad) |
| is_cover | TINYINT(1) DEFAULT 0 | Imagen principal |
| sort_order | SMALLINT DEFAULT 0 | Orden en galería |
| created_at / updated_at | TIMESTAMP | |

### `settings`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BIGINT UNSIGNED PK | |
| key | VARCHAR(100) UNIQUE NOT NULL | Clave del setting |
| value | TEXT NULL | Valor almacenado |
| type | ENUM('string','boolean','integer','json') DEFAULT 'string' | Tipo para casting |
| label | VARCHAR(150) NULL | Etiqueta legible para el admin |
| group | VARCHAR(60) DEFAULT 'general' | Agrupación (general, contact, seo…) |
| is_public | TINYINT(1) DEFAULT 0 | Accesible sin autenticación |
| created_at / updated_at | TIMESTAMP | |

### Tablas gestionadas por paquetes

- `permissions`, `roles`, `model_has_permissions`, `model_has_roles`, `role_has_permissions` — Spatie.
- `personal_access_tokens` — Laravel Sanctum.

---

## 5. Endpoints

### Auth

| Método | URI | Descripción | Auth |
|--------|-----|-------------|------|
| POST | `/api/v1/auth/login` | Login email/password → token | Público |
| POST | `/api/v1/auth/logout` | Revoca token actual | Autenticado |
| GET | `/api/v1/auth/me` | Datos del usuario autenticado | Autenticado |

### Tokens (gestión chatbot)

| Método | URI | Descripción | Auth |
|--------|-----|-------------|------|
| GET | `/api/v1/tokens` | Listar tokens activos | `super_admin` |
| POST | `/api/v1/tokens` | Crear token con abilities limitadas | `super_admin` |
| DELETE | `/api/v1/tokens/{id}` | Revocar token | `super_admin` |

### Categories

| Método | URI | Descripción | Auth |
|--------|-----|-------------|------|
| GET | `/api/v1/categories` | Listar categorías activas (paginado) | Público |
| GET | `/api/v1/categories/{slug}` | Detalle de categoría | Público |
| POST | `/api/v1/categories` | Crear categoría | `admin`, `super_admin` |
| PUT | `/api/v1/categories/{id}` | Actualizar categoría | `admin`, `super_admin` |
| DELETE | `/api/v1/categories/{id}` | Desactivar categoría (status=0) | `super_admin` |

### Products

| Método | URI | Descripción | Auth |
|--------|-----|-------------|------|
| GET | `/api/v1/products` | Listar productos (filtros: category, featured, search) | Público |
| GET | `/api/v1/products/{slug}` | Detalle con imágenes y categoría | Público |
| POST | `/api/v1/products` | Crear producto | `admin`, `super_admin` |
| PUT | `/api/v1/products/{id}` | Actualizar producto | `admin`, `super_admin` |
| DELETE | `/api/v1/products/{id}` | Desactivar producto (status=0) | `super_admin` |
| POST | `/api/v1/products/{id}/images` | Subir imágenes | `admin`, `super_admin` |
| DELETE | `/api/v1/products/{id}/images/{imageId}` | Eliminar imagen | `admin`, `super_admin` |
| PATCH | `/api/v1/products/{id}/images/{imageId}/cover` | Marcar como portada | `admin`, `super_admin` |

### Users

| Método | URI | Descripción | Auth |
|--------|-----|-------------|------|
| GET | `/api/v1/users` | Listar usuarios | `super_admin` |
| GET | `/api/v1/users/{id}` | Detalle de usuario | `super_admin` |
| POST | `/api/v1/users` | Crear usuario | `super_admin` |
| PUT | `/api/v1/users/{id}` | Actualizar usuario | `super_admin` |
| PATCH | `/api/v1/users/{id}/toggle-status` | Activar/desactivar | `super_admin` |
| DELETE | `/api/v1/users/{id}` | Desactivar usuario (status=0) | `super_admin` |

### Settings

| Método | URI | Descripción | Auth |
|--------|-----|-------------|------|
| GET | `/api/v1/settings` | Listar todos los settings | `admin`, `super_admin` |
| GET | `/api/v1/settings/public` | Settings públicos (is_public=1) | Público / chatbot |
| PATCH | `/api/v1/settings/bulk` | Actualizar múltiples settings | `super_admin` |
| PUT | `/api/v1/settings/{key}` | Actualizar un setting | `super_admin` |

**Total: 26 endpoints**

### Formato de respuesta estandarizado

```json
{
  "success": true,
  "message": "Operación exitosa",
  "data": { "..." : "..." },
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 48,
    "last_page": 4
  }
}
```

---

## 6. Seguridad

### Autenticación

- **Laravel Sanctum** con tokens Bearer.
- Tokens SPA: duración 8 horas, nombre `spa-session`, abilities `['*']`.
- Tokens chatbot: sin expiración, abilities específicas (`products:read`, `settings:read`).
- Middleware `ForceJsonResponse` garantiza que todos los errores 4xx/5xx retornen JSON (nunca HTML).
- Rate limiting: `throttle:60,1` en rutas públicas, `throttle:10,1` en login.

### Autorización

- Roles vía **Spatie Laravel Permission**: `super_admin`, `admin`.
- **Policies** por modelo con hook `before()` que otorga acceso total al `super_admin`.
- Rutas protegidas por middleware `role:super_admin` o `role:admin|super_admin`.
- Endpoints públicos del catálogo no requieren token (chatbot puede operar sin auth en `GET /products`, `GET /categories`, `GET /settings/public`).

### Buenas prácticas

- Contraseñas almacenadas con **bcrypt** (`BCRYPT_ROUNDS=12`).
- Tokens Sanctum almacenados como **hash SHA-256** en BD (nunca el token plano).
- **CORS** configurado para orígenes específicos del SPA (`localhost:3000`, `localhost:5173`) con `supports_credentials: true`.
- Validación estricta con **Form Requests** — nunca validación inline en controllers.
- Nunca se eliminan registros físicamente — siempre `status = 0`.

---

## 7. Estructura de Carpetas

```
hiloblanco-backend/
├── app/
│   ├── Console/Commands/
│   │   └── PurgeExpiredTokens.php        # Limpieza de tokens vencidos (daily)
│   ├── Http/
│   │   ├── Controllers/Api/V1/
│   │   │   ├── Auth/
│   │   │   │   ├── AuthController.php    # login, logout, me
│   │   │   │   └── TokenController.php   # CRUD tokens chatbot
│   │   │   ├── Products/
│   │   │   │   └── ProductController.php
│   │   │   ├── Categories/
│   │   │   │   └── CategoryController.php
│   │   │   ├── Users/
│   │   │   │   └── UserController.php
│   │   │   └── Settings/
│   │   │       └── SettingController.php
│   │   ├── Middleware/
│   │   │   └── ForceJsonResponse.php
│   │   ├── Requests/
│   │   │   ├── Auth/LoginRequest.php
│   │   │   ├── Products/
│   │   │   │   ├── StoreProductRequest.php
│   │   │   │   └── UpdateProductRequest.php
│   │   │   ├── Categories/
│   │   │   │   ├── StoreCategoryRequest.php
│   │   │   │   └── UpdateCategoryRequest.php
│   │   │   ├── Users/
│   │   │   │   ├── StoreUserRequest.php
│   │   │   │   └── UpdateUserRequest.php
│   │   │   └── Settings/
│   │   │       ├── UpdateSettingRequest.php
│   │   │       └── BulkSettingRequest.php
│   │   └── Resources/
│   │       ├── Auth/UserAuthResource.php
│   │       ├── Products/
│   │       │   ├── ProductResource.php
│   │       │   └── ProductCollection.php
│   │       ├── Categories/
│   │       │   ├── CategoryResource.php
│   │       │   └── CategoryCollection.php
│   │       ├── Users/
│   │       │   ├── UserResource.php
│   │       │   └── UserCollection.php
│   │       └── Settings/
│   │           └── SettingResource.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Category.php
│   │   ├── Product.php
│   │   ├── ProductImage.php
│   │   └── Setting.php
│   ├── Policies/
│   │   ├── ProductPolicy.php
│   │   ├── CategoryPolicy.php
│   │   ├── UserPolicy.php
│   │   └── SettingPolicy.php
│   ├── Providers/
│   │   └── AppServiceProvider.php        # Registro de Policies
│   └── Traits/
│       └── ApiResponse.php               # Respuestas estandarizadas
├── database/
│   ├── migrations/                       # Ordenadas cronológicamente
│   ├── seeders/
│   │   ├── DatabaseSeeder.php
│   │   ├── RoleSeeder.php
│   │   ├── UserSeeder.php
│   │   ├── CategorySeeder.php
│   │   ├── ProductSeeder.php
│   │   └── SettingSeeder.php
│   └── factories/
│       ├── CategoryFactory.php
│       └── ProductFactory.php
├── routes/
│   └── api.php                           # Todas las rutas bajo /api/v1/
├── storage/app/public/products/          # Imágenes subidas
├── config/
│   ├── cors.php                          # allowed_origins + supports_credentials
│   └── permission.php                    # Spatie config
├── tests/Feature/
│   ├── Auth/AuthTest.php
│   ├── Products/ProductTest.php
│   └── Categories/CategoryTest.php
├── REQUERIMIENTOS.md
└── .env
```

---

## 8. Convenciones del Proyecto

| Convención | Detalle |
|------------|---------|
| **Soft-delete** | Columna `status` (1/0) en todas las entidades. No se usa `SoftDeletes` de Eloquent. |
| **Respuesta AJAX** | `response()->json(['success' => true/false, 'data' => ..., 'message' => ...])` |
| **Versionado** | Todas las rutas bajo `/api/v1/` para migración sin breaking changes. |
| **Slugs** | Generados con `Str::slug()` al crear. Únicos por tabla. Se regeneran si cambia el nombre. |
| **Formateo** | PSR-12 con `./vendor/bin/pint` antes de cada commit. |
| **Tests** | Pest PHP v3. Un archivo por módulo en `tests/Feature/`. |
| **Imágenes** | `storage/app/public/products/` accesibles vía symlink `storage/`. |
| **Passwords** | `BCRYPT_ROUNDS=12`. Nunca en texto plano. |

---

## 9. Credenciales Iniciales (Seeders)

> **IMPORTANTE**: Cambiar todas las contraseñas antes de pasar a producción.

| Usuario | Email | Password | Rol |
|---------|-------|----------|-----|
| HiloBlanco Admin | admin@hiloblanco.com | H1loBlanco#2024 | super_admin |
| Operaciones HiloBlanco | operaciones@hiloblanco.com | Operaciones#2024 | admin |

---

## 10. Settings Iniciales

| key | value | group | Público |
|-----|-------|-------|---------|
| site_name | HiloBlanco | general | Sí |
| site_tagline | Vestidos de novia únicos | general | Sí |
| whatsapp_number | +573001234567 | contact | Sí |
| instagram_handle | @hiloblanco | social | Sí |
| email_contact | info@hiloblanco.com | contact | Sí |
| address | Medellín, Colombia | contact | Sí |
| currency | COP | general | Sí |
| show_prices | 0 (boolean) | catalog | Sí |
| appointment_enabled | 0 (boolean) | features | No |
| meta_description | Boutique de vestidos de novia... | seo | No |

> **Política de negocio — `show_prices: false`**: Los precios de los vestidos son siempre `NULL`
> en base de datos y nunca se muestran públicamente. El precio se entrega bajo consulta directa
> vía WhatsApp (`whatsapp_number`). El setting `show_prices` (is_public=1) permite que el
> frontend React y el chatbot externo lean este flag y omitan cualquier UI de precio.
> Esta política es definitiva y no se invierte sin aprobación de la dirección de HiloBlanco.
