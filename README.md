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

```bash
php artisan db:seed --class="Kaely\AuthPackage\Database\Seeders\AuthPackageSeeder"
```

### 6. Instalación automática (recomendado)

```bash
php artisan auth-package:install
```

**Nota:** El comando de instalación automática verificará si las tablas básicas de Laravel (`users`, `personal_access_tokens`) existen y las creará si es necesario.

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
        'prefix' => 'api/v1/auth',
        'middleware' => ['api'],
        'auth_middleware' => ['auth:sanctum'],
    ],
];
```

## Uso

### Rutas disponibles

#### Autenticación

- `POST /api/v1/auth/login` - Iniciar sesión
- `POST /api/v1/auth/register` - Registrar usuario
- `POST /api/v1/auth/logout` - Cerrar sesión (requiere auth)
- `GET /api/v1/auth/me` - Obtener usuario actual (requiere auth)
- `POST /api/v1/auth/refresh` - Refrescar token (requiere auth)

#### Menú Dinámico

- `GET /api/v1/auth/menu` - Obtener menú dinámico del usuario
- `GET /api/v1/auth/menu/permissions` - Obtener permisos del usuario
- `POST /api/v1/auth/menu/has-permission` - Verificar permiso específico
- `POST /api/v1/auth/menu/has-any-permission` - Verificar múltiples permisos
- `GET /api/v1/auth/menu/modules` - Obtener módulos accesibles

#### Gestión de Sucursales (Branches)

- `GET /api/v1/auth/branches` - Listar sucursales
- `POST /api/v1/auth/branches` - Crear sucursal
- `GET /api/v1/auth/branches/{id}` - Obtener sucursal
- `PUT /api/v1/auth/branches/{id}` - Actualizar sucursal
- `DELETE /api/v1/auth/branches/{id}` - Eliminar sucursal
- `GET /api/v1/auth/branches/active` - Sucursales activas

#### Gestión de Departamentos

- `GET /api/v1/auth/departments` - Listar departamentos
- `POST /api/v1/auth/departments` - Crear departamento
- `GET /api/v1/auth/departments/{id}` - Obtener departamento
- `PUT /api/v1/auth/departments/{id}` - Actualizar departamento
- `DELETE /api/v1/auth/departments/{id}` - Eliminar departamento
- `GET /api/v1/auth/departments/active` - Departamentos activos
- `GET /api/v1/auth/departments/by-branch/{branchId}` - Departamentos por sucursal

#### Gestión de Módulos

- `GET /api/v1/auth/modules` - Listar módulos
- `POST /api/v1/auth/modules` - Crear módulo
- `GET /api/v1/auth/modules/{id}` - Obtener módulo
- `PUT /api/v1/auth/modules/{id}` - Actualizar módulo
- `DELETE /api/v1/auth/modules/{id}` - Eliminar módulo
- `GET /api/v1/auth/modules/active` - Módulos activos
- `POST /api/v1/auth/modules/update-order` - Actualizar orden de módulos

#### Gestión de Permisos

- `GET /api/v1/auth/permissions` - Listar permisos
- `POST /api/v1/auth/permissions` - Crear permiso
- `GET /api/v1/auth/permissions/{id}` - Obtener permiso
- `PUT /api/v1/auth/permissions/{id}` - Actualizar permiso
- `DELETE /api/v1/auth/permissions/{id}` - Eliminar permiso
- `GET /api/v1/auth/permissions/active` - Permisos activos
- `GET /api/v1/auth/permissions/by-module/{moduleId}` - Permisos por módulo
- `POST /api/v1/auth/permissions/bulk-create` - Crear permisos en lote

#### Gestión de Roles

- `GET /api/v1/auth/roles` - Listar roles
- `POST /api/v1/auth/roles` - Crear rol
- `GET /api/v1/auth/roles/{id}` - Obtener rol
- `PUT /api/v1/auth/roles/{id}` - Actualizar rol
- `DELETE /api/v1/auth/roles/{id}` - Eliminar rol
- `GET /api/v1/auth/roles/active` - Roles activos
- `POST /api/v1/auth/roles/{id}/assign-permissions` - Asignar permisos a rol
- `GET /api/v1/auth/roles/{id}/permissions` - Permisos del rol

#### Gestión de Usuarios

- `GET /api/v1/auth/users` - Listar usuarios
- `POST /api/v1/auth/users` - Crear usuario
- `GET /api/v1/auth/users/{id}` - Obtener usuario
- `PUT /api/v1/auth/users/{id}` - Actualizar usuario
- `DELETE /api/v1/auth/users/{id}` - Eliminar usuario
- `GET /api/v1/auth/users/by-branch/{branchId}` - Usuarios por sucursal
- `GET /api/v1/auth/users/by-department/{departmentId}` - Usuarios por departamento
- `POST /api/v1/auth/users/{id}/assign-roles` - Asignar roles a usuario
- `GET /api/v1/auth/users/{id}/roles` - Roles del usuario
- `GET /api/v1/auth/users/{id}/permissions` - Permisos del usuario

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