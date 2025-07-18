# Kaely Auth Package

Un paquete de autenticación completo para Laravel con sistema de roles y permisos.

## Características

- ✅ Autenticación con Laravel Sanctum
- ✅ Sistema de roles y permisos
- ✅ Gestión de usuarios
- ✅ Gestión de módulos
- ✅ Middleware para verificación de roles y permisos
- ✅ API RESTful completa
- ✅ Documentación OpenAPI
- ✅ Migraciones y seeders incluidos
- ✅ Configuración flexible con variables de entorno
- ✅ **Extiende las tablas existentes de Laravel** (no las recrea)
- ✅ Tests completos incluidos

## Instalación

### 1. Instalar el paquete

```bash
composer require kaely/auth-package
```

### 2. Publicar las configuraciones

```bash
php artisan vendor:publish --tag=auth-package-config
```

### 3. Publicar las migraciones

```bash
php artisan vendor:publish --tag=auth-package-migrations
```

### 4. Ejecutar las migraciones

```bash
php artisan migrate
```

### 5. Ejecutar los seeders

**Opción 1: Publicar y ejecutar (recomendado)**
```bash
# Publicar el seeder
php artisan vendor:publish --provider="Kaely\AuthPackage\AuthPackageServiceProvider" --tag=auth-package-seeders

# Ejecutar el seeder
php artisan db:seed --class=AuthPackageSeeder
```

**Opción 2: Ejecutar directamente**
```bash
php artisan db:seed --class="Kaely\AuthPackage\Database\Seeders\AuthPackageSeeder"
```

### 6. Instalación automática (recomendado)

```bash
php artisan auth-package:install
```

**Nota:** El comando de instalación automática:
- Publicará configuración y migraciones
- Ejecutará migraciones y seeders
- Verificará que las tablas básicas de Laravel (`users`, `personal_access_tokens`) existan
- Creará un usuario administrador por defecto

### 7. Reparar problemas de migración (si es necesario)

Si encuentras errores de migración, puedes usar el comando de reparación:

```bash
php artisan auth-package:fix-migrations
```

Este comando detectará y solucionará automáticamente problemas comunes como:
- Columnas faltantes en tablas
- Estructura de base de datos incompleta
- Problemas de migración

## Configuración

### Variables de Entorno

El paquete utiliza variables de entorno para toda su configuración. Puedes configurar estas variables en tu archivo `.env`:

```env
# Authentication Configuration
AUTH_GUARD=sanctum
AUTH_PROVIDER=users
AUTH_PASSWORD_TIMEOUT=10800

# Token Configuration
AUTH_TOKEN_EXPIRATION=604800
AUTH_REFRESH_TOKEN_EXPIRATION=2592000

# Roles and Permissions Configuration
AUTH_ROLES_CACHE_TTL=3600
AUTH_DEFAULT_ROLE=user

# Password Validation Configuration
AUTH_PASSWORD_MIN_LENGTH=8
AUTH_PASSWORD_REQUIRE_SPECIAL=false
AUTH_PASSWORD_REQUIRE_NUMBERS=true
AUTH_PASSWORD_REQUIRE_UPPERCASE=true

# Routes Configuration
AUTH_ROUTES_PREFIX=auth
AUTH_ROUTES_API_PREFIX=api
AUTH_ROUTES_VERSION_PREFIX=v1
AUTH_ROUTES_MIDDLEWARE=api
AUTH_ROUTES_AUTH_MIDDLEWARE=auth:sanctum
AUTH_ROUTES_ENABLE_VERSIONING=true
AUTH_ROUTES_AUTO_API_PREFIX=true

# Response Configuration
AUTH_INCLUDE_USER_ROLES=true
AUTH_INCLUDE_USER_PERMISSIONS=true
```

### Configuración básica

El archivo de configuración se encuentra en `config/auth-package.php` y utiliza las variables de entorno:

```php
return [
    'models' => [
        'user' => \Kaely\AuthPackage\Models\User::class,
        'role' => \Kaely\AuthPackage\Models\Role::class,
        'permission' => \Kaely\AuthPackage\Models\Permission::class,
        'role_category' => \Kaely\AuthPackage\Models\RoleCategory::class,
        'module' => \Kaely\AuthPackage\Models\Module::class,
        'person' => \Kaely\AuthPackage\Models\Person::class,
    ],
    
    'auth' => [
        'guard' => env('AUTH_GUARD', 'sanctum'),
        'provider' => env('AUTH_PROVIDER', 'users'),
        'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800), // 3 horas
    ],
    
    'tokens' => [
        'expiration' => env('AUTH_TOKEN_EXPIRATION', 60 * 24 * 7), // 7 días
        'refresh_expiration' => env('AUTH_REFRESH_TOKEN_EXPIRATION', 60 * 24 * 30), // 30 días
    ],
    
    'validation' => [
        'password_min_length' => env('AUTH_PASSWORD_MIN_LENGTH', 8),
        'password_require_special' => env('AUTH_PASSWORD_REQUIRE_SPECIAL', false),
        'password_require_numbers' => env('AUTH_PASSWORD_REQUIRE_NUMBERS', true),
        'password_require_uppercase' => env('AUTH_PASSWORD_REQUIRE_UPPERCASE', true),
    ],
    
    'routes' => [
        'prefix' => env('AUTH_ROUTES_PREFIX', 'auth'),
        'api_prefix' => env('AUTH_ROUTES_API_PREFIX', 'api'),
        'version_prefix' => env('AUTH_ROUTES_VERSION_PREFIX', null),
        'middleware' => explode(',', env('AUTH_ROUTES_MIDDLEWARE', 'api')),
        'auth_middleware' => explode(',', env('AUTH_ROUTES_AUTH_MIDDLEWARE', 'auth:sanctum')),
        'enable_versioning' => env('AUTH_ROUTES_ENABLE_VERSIONING', false),
        'auto_api_prefix' => env('AUTH_ROUTES_AUTO_API_PREFIX', true),
    ],
];
```

## Configuración de Rutas

El paquete permite configurar las rutas de manera muy flexible. Puedes personalizar los prefijos según tus necesidades:

### Ejemplos de Configuración

#### 1. Rutas simples: `/auth/`
```env
AUTH_ROUTES_PREFIX=auth
AUTH_ROUTES_API_PREFIX=
AUTH_ROUTES_VERSION_PREFIX=
AUTH_ROUTES_ENABLE_VERSIONING=false
AUTH_ROUTES_AUTO_API_PREFIX=false
```

#### 2. Rutas con prefijo API: `/api/auth/`
```env
AUTH_ROUTES_PREFIX=auth
AUTH_ROUTES_API_PREFIX=api
AUTH_ROUTES_VERSION_PREFIX=
AUTH_ROUTES_ENABLE_VERSIONING=false
AUTH_ROUTES_AUTO_API_PREFIX=true
```

#### 3. Rutas con versionado: `/api/v1/auth/`
```env
AUTH_ROUTES_PREFIX=auth
AUTH_ROUTES_API_PREFIX=api
AUTH_ROUTES_VERSION_PREFIX=v1
AUTH_ROUTES_ENABLE_VERSIONING=true
AUTH_ROUTES_AUTO_API_PREFIX=true
```

#### 4. Rutas completamente personalizadas: `/my-api/auth/`
```env
AUTH_ROUTES_PREFIX=auth
AUTH_ROUTES_API_PREFIX=my-api
AUTH_ROUTES_VERSION_PREFIX=
AUTH_ROUTES_ENABLE_VERSIONING=false
AUTH_ROUTES_AUTO_API_PREFIX=true
```

### Parámetros de Configuración

- `AUTH_ROUTES_PREFIX`: Prefijo base para todas las rutas (por defecto: `'auth'`)
- `AUTH_ROUTES_API_PREFIX`: Prefijo de API opcional (por defecto: `'api'`)
- `AUTH_ROUTES_VERSION_PREFIX`: Prefijo de versión opcional (por defecto: `null`)
- `AUTH_ROUTES_AUTO_API_PREFIX`: Si agregar automáticamente el prefijo de API (por defecto: `true`)
- `AUTH_ROUTES_ENABLE_VERSIONING`: Si habilitar el versionado automático (por defecto: `false`)
- `AUTH_ROUTES_MIDDLEWARE`: Middleware para todas las rutas (por defecto: `'api'`)
- `AUTH_ROUTES_AUTH_MIDDLEWARE`: Middleware para rutas protegidas (por defecto: `'auth:sanctum'`)

Para más detalles sobre la configuración de rutas, consulta [ROUTES_CONFIGURATION.md](ROUTES_CONFIGURATION.md).

## Uso

### Rutas disponibles

#### Autenticación

- `POST /{prefix}/login` - Iniciar sesión
- `POST /{prefix}/register` - Registrar usuario
- `POST /{prefix}/logout` - Cerrar sesión (requiere auth)
- `GET /{prefix}/me` - Obtener usuario actual (requiere auth)
- `POST /{prefix}/refresh` - Refrescar token (requiere auth)

#### Menú Dinámico

- `GET /{prefix}/menu` - Obtener menú dinámico del usuario
- `GET /{prefix}/menu/permissions` - Obtener permisos del usuario
- `POST /{prefix}/menu/has-permission` - Verificar permiso específico
- `POST /{prefix}/menu/has-any-permission` - Verificar múltiples permisos
- `GET /{prefix}/menu/modules` - Obtener módulos accesibles

#### Gestión de Módulos

- `GET /{prefix}/modules` - Listar módulos
- `POST /{prefix}/modules` - Crear módulo
- `GET /{prefix}/modules/{id}` - Obtener módulo
- `PUT /{prefix}/modules/{id}` - Actualizar módulo
- `DELETE /{prefix}/modules/{id}` - Eliminar módulo
- `GET /{prefix}/modules/active` - Módulos activos
- `POST /{prefix}/modules/update-order` - Actualizar orden de módulos

#### Gestión de Permisos

- `GET /{prefix}/permissions` - Listar permisos
- `POST /{prefix}/permissions` - Crear permiso
- `GET /{prefix}/permissions/{id}` - Obtener permiso
- `PUT /{prefix}/permissions/{id}` - Actualizar permiso
- `DELETE /{prefix}/permissions/{id}` - Eliminar permiso
- `GET /{prefix}/permissions/active` - Permisos activos
- `GET /{prefix}/permissions/by-module/{moduleId}` - Permisos por módulo
- `POST /{prefix}/permissions/bulk-create` - Crear permisos en lote

#### Gestión de Roles

- `GET /{prefix}/roles` - Listar roles
- `POST /{prefix}/roles` - Crear rol
- `GET /{prefix}/roles/{id}` - Obtener rol
- `PUT /{prefix}/roles/{id}` - Actualizar rol
- `DELETE /{prefix}/roles/{id}` - Eliminar rol
- `GET /{prefix}/roles/active` - Roles activos
- `POST /{prefix}/roles/{id}/assign-permissions` - Asignar permisos a rol
- `GET /{prefix}/roles/{id}/permissions` - Permisos del rol

#### Gestión de Usuarios

- `GET /{prefix}/users` - Listar usuarios
- `POST /{prefix}/users` - Crear usuario
- `GET /{prefix}/users/{id}` - Obtener usuario
- `PUT /{prefix}/users/{id}` - Actualizar usuario
- `DELETE /{prefix}/users/{id}` - Eliminar usuario
- `POST /{prefix}/users/{id}/assign-roles` - Asignar roles a usuario
- `GET /{prefix}/users/{id}/roles` - Roles del usuario
- `GET /{prefix}/users/{id}/permissions` - Permisos del usuario

### Middleware

El paquete incluye middleware para verificar roles y permisos:

```php
// Verificar rol
Route::middleware('auth.role:admin')->group(function () {
    // Rutas que requieren rol admin
});

// Verificar permiso
Route::middleware('auth.permission:users.create')->group(function () {
    // Rutas que requieren permiso users.create
});
```

### Modelos

#### User

```php
use Kaely\AuthPackage\Models\User;

// Verificar rol
if ($user->hasRole('admin')) {
    // Usuario tiene rol admin
}

// Verificar permiso
if ($user->hasPermission('users.create')) {
    // Usuario tiene permiso para crear usuarios
}

// Obtener roles
$roles = $user->roles;

// Obtener permisos
$permissions = $user->roles->flatMap->permissions;
```

#### Role

```php
use Kaely\AuthPackage\Models\Role;

$role = Role::find(1);

// Verificar permiso
if ($role->hasPermission('users.create')) {
    // El rol tiene permiso para crear usuarios
}

// Asignar permisos
$role->assignPermissions(['users.create', 'users.edit']);
```

### Credenciales por defecto

Después de ejecutar los seeders, se crea un usuario administrador:

- **Email:** admin@example.com
- **Password:** password

## Testing

El paquete incluye una suite completa de tests que cubre todas las funcionalidades:

### Ejecutar tests

```bash
# Ejecutar todos los tests
php artisan test

# Ejecutar tests específicos
php artisan test --filter=AuthControllerTest
php artisan test --filter=UserControllerTest
php artisan test --filter=RoleControllerTest
php artisan test --filter=MenuControllerTest
```

### Tests incluidos

- **AuthControllerTest**: Tests de autenticación (login, register, logout, etc.)
- **UserControllerTest**: Tests de gestión de usuarios (CRUD, roles, permisos)
- **RoleControllerTest**: Tests de gestión de roles (CRUD, permisos)
- **MenuControllerTest**: Tests de menú dinámico y verificación de permisos

### Configuración de tests

Los tests utilizan SQLite en memoria para mayor velocidad y aislamiento. La configuración se maneja automáticamente en el `TestCase` base.

## Estructura de la base de datos

### Tablas extendidas de Laravel

- `users` - **Extendida** con campos adicionales (is_active, soft deletes, auditoría)
- `personal_access_tokens` - **Usada** por Sanctum (creada automáticamente)

### Tablas nuevas del paquete

- `roles` - Roles de usuario
- `permissions` - Permisos del sistema
- `modules` - Módulos del sistema
- `role_categories` - Categorías de roles
- `people` - Información personal de usuarios

### Tablas pivot

- `user_role` - Relación usuarios-roles
- `role_permission` - Relación roles-permisos

### Campos adicionales en users

El paquete agrega los siguientes campos a la tabla `users` existente:

- `is_active` (boolean) - Estado activo/inactivo del usuario
- `deleted_at` (timestamp) - Soft deletes
- `user_add` (unsignedBigInteger) - ID del usuario que creó el registro
- `user_edit` (unsignedBigInteger) - ID del usuario que editó por última vez
- `user_deleted` (unsignedBigInteger) - ID del usuario que eliminó el registro

## Personalización

### Extender modelos

```php
use Kaely\AuthPackage\Models\User as BaseUser;

class User extends BaseUser
{
    // Agregar funcionalidad personalizada
}
```

### Configurar respuestas

```env
AUTH_INCLUDE_USER_ROLES=true
AUTH_INCLUDE_USER_PERMISSIONS=true
```

## Compatibilidad

### Laravel 12.x
- ✅ Compatible con Laravel 12
- ✅ Usa Sanctum para autenticación API
- ✅ Extiende las migraciones por defecto de Laravel

### Migraciones existentes
- ✅ **No interfiere** con las migraciones existentes de Laravel
- ✅ **Extiende** la tabla `users` con campos adicionales
- ✅ **Usa** la tabla `personal_access_tokens` de Sanctum
- ✅ **Agrega** nuevas tablas para roles, permisos, etc.

## Solución de Problemas

Si encuentras problemas durante la instalación o uso del paquete, consulta [TROUBLESHOOTING.md](TROUBLESHOOTING.md) para soluciones comunes.

## Contribuir

1. Fork el proyecto
2. Crear una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abrir un Pull Request 