# Kaely Auth Package

Un paquete de autenticación completo para Laravel con sistema de roles y permisos.

## Características

- ✅ Autenticación con Laravel Sanctum
- ✅ Sistema de roles y permisos
- ✅ Gestión de usuarios
- ✅ Gestión de módulos
- ✅ Gestión de sucursales y departamentos
- ✅ Middleware para verificación de roles y permisos
- ✅ API RESTful completa
- ✅ Documentación OpenAPI
- ✅ Migraciones y seeders incluidos
- ✅ Configuración flexible
- ✅ **Extiende las tablas existentes de Laravel** (no las recrea)

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

### Configuración básica

El archivo de configuración se encuentra en `config/auth-package.php`:

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
        'guard' => 'sanctum',
        'provider' => 'users',
        'password_timeout' => 10800, // 3 horas
    ],
    
    'tokens' => [
        'expiration' => 60 * 24 * 7, // 7 días
        'refresh_expiration' => 60 * 24 * 30, // 30 días
    ],
    
    'validation' => [
        'password_min_length' => 8,
        'password_require_special' => false,
        'password_require_numbers' => true,
        'password_require_uppercase' => true,
    ],
    
    'routes' => [
        'prefix' => 'auth', // Prefijo base sin api/v1
        'api_prefix' => 'api', // Prefijo de API (opcional)
        'version_prefix' => null, // Prefijo de versión (opcional, ej: v1, v2)
        'middleware' => ['api'],
        'auth_middleware' => ['auth:sanctum'],
        'enable_versioning' => false, // Habilitar versionado automático
        'auto_api_prefix' => true, // Agregar automáticamente el prefijo api
    ],
];
```

## Configuración de Rutas

El paquete permite configurar las rutas de manera muy flexible. Puedes personalizar los prefijos según tus necesidades:

### Ejemplos de Configuración

#### 1. Rutas simples: `/auth/`
```php
'routes' => [
    'prefix' => 'auth',
    'api_prefix' => null,
    'version_prefix' => null,
    'auto_api_prefix' => false,
    'enable_versioning' => false,
],
```

#### 2. Rutas con prefijo API: `/api/auth/`
```php
'routes' => [
    'prefix' => 'auth',
    'api_prefix' => 'api',
    'version_prefix' => null,
    'auto_api_prefix' => true,
    'enable_versioning' => false,
],
```

#### 3. Rutas con versionado: `/api/v1/auth/`
```php
'routes' => [
    'prefix' => 'auth',
    'api_prefix' => 'api',
    'version_prefix' => 'v1',
    'auto_api_prefix' => true,
    'enable_versioning' => true,
],
```

#### 4. Rutas completamente personalizadas: `/my-api/auth/`
```php
'routes' => [
    'prefix' => 'auth',
    'api_prefix' => 'my-api',
    'version_prefix' => null,
    'auto_api_prefix' => true,
    'enable_versioning' => false,
],
```

### Parámetros de Configuración

- `prefix`: Prefijo base para todas las rutas (por defecto: `'auth'`)
- `api_prefix`: Prefijo de API opcional (por defecto: `'api'`)
- `version_prefix`: Prefijo de versión opcional (por defecto: `null`)
- `auto_api_prefix`: Si agregar automáticamente el prefijo de API (por defecto: `true`)
- `enable_versioning`: Si habilitar el versionado automático (por defecto: `false`)
- `middleware`: Middleware para todas las rutas (por defecto: `['api']`)
- `auth_middleware`: Middleware para rutas protegidas (por defecto: `['auth:sanctum']`)

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

#### Gestión de Sucursales (Branches)

- `GET /{prefix}/branches` - Listar sucursales
- `POST /{prefix}/branches` - Crear sucursal
- `GET /{prefix}/branches/{id}` - Obtener sucursal
- `PUT /{prefix}/branches/{id}` - Actualizar sucursal
- `DELETE /{prefix}/branches/{id}` - Eliminar sucursal
- `GET /{prefix}/branches/active` - Sucursales activas

#### Gestión de Departamentos

- `GET /{prefix}/departments` - Listar departamentos
- `POST /{prefix}/departments` - Crear departamento
- `GET /{prefix}/departments/{id}` - Obtener departamento
- `PUT /{prefix}/departments/{id}` - Actualizar departamento
- `DELETE /{prefix}/departments/{id}` - Eliminar departamento
- `GET /{prefix}/departments/active` - Departamentos activos
- `GET /{prefix}/departments/by-branch/{branchId}` - Departamentos por sucursal

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
- `GET /{prefix}/users/by-branch/{branchId}` - Usuarios por sucursal
- `GET /{prefix}/users/by-department/{departmentId}` - Usuarios por departamento
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

## Estructura de la base de datos

### Tablas extendidas de Laravel

- `users` - **Extendida** con campos adicionales (is_active, soft deletes, auditoría)
- `personal_access_tokens` - **Usada** por Sanctum (creada automáticamente)

## Solución de Problemas

Si encuentras problemas durante la instalación o uso del paquete, consulta [TROUBLESHOOTING.md](TROUBLESHOOTING.md) para soluciones comunes.

### Tablas nuevas del paquete

- `roles` - Roles de usuario
- `permissions` - Permisos del sistema
- `modules` - Módulos del sistema
- `role_categories` - Categorías de roles
- `people` - Información personal de usuarios
- `branches` - Sucursales
- `departments` - Departamentos

### Tablas pivot

- `user_role` - Relación usuarios-roles
- `role_permission` - Relación roles-permisos
- `branches_users` - Relación sucursales-usuarios
- `departments_users` - Relación departamentos-usuarios

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

```php
'responses' => [
    'include_user_roles' => true,
    'include_user_permissions' => true,
    'include_user_branches' => true,
    'include_user_departments' => true,
],
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

## Testing

```bash
php artisan test
```

## Contribuir

1. Fork el proyecto
2. Crear una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abrir un Pull Request

## Licencia

Este paquete está bajo la licencia MIT. Ver el archivo `LICENSE` para más detalles.

## Soporte

Para soporte, por favor abrir un issue en el repositorio del proyecto. 