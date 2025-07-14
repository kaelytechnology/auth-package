# Configuración de Rutas del Paquete de Autenticación

Este documento explica cómo configurar las rutas del paquete de autenticación de manera flexible para adaptarse a diferentes necesidades de proyecto.

## Configuración Básica

El paquete permite configurar las rutas de manera muy flexible. Las opciones de configuración se encuentran en `config/auth-package.php` en la sección `routes`:

```php
'routes' => [
    'prefix' => 'auth', // Prefijo base sin api/v1
    'api_prefix' => 'api', // Prefijo de API (opcional)
    'version_prefix' => null, // Prefijo de versión (opcional, ej: v1, v2)
    'middleware' => ['api'],
    'auth_middleware' => ['auth:sanctum'],
    'enable_versioning' => false, // Habilitar versionado automático
    'auto_api_prefix' => true, // Agregar automáticamente el prefijo api
],
```

## Ejemplos de Configuración

### 1. Rutas simples: `/auth/`
```php
'routes' => [
    'prefix' => 'auth',
    'api_prefix' => null,
    'version_prefix' => null,
    'auto_api_prefix' => false,
    'enable_versioning' => false,
],
```

**Resultado:** `/auth/login`, `/auth/register`, etc.

### 2. Rutas con prefijo API: `/api/auth/`
```php
'routes' => [
    'prefix' => 'auth',
    'api_prefix' => 'api',
    'version_prefix' => null,
    'auto_api_prefix' => true,
    'enable_versioning' => false,
],
```

**Resultado:** `/api/auth/login`, `/api/auth/register`, etc.

### 3. Rutas con versionado: `/api/v1/auth/`
```php
'routes' => [
    'prefix' => 'auth',
    'api_prefix' => 'api',
    'version_prefix' => 'v1',
    'auto_api_prefix' => true,
    'enable_versioning' => true,
],
```

**Resultado:** `/api/v1/auth/login`, `/api/v1/auth/register`, etc.

### 4. Rutas con versión personalizada: `/api/v2/auth/`
```php
'routes' => [
    'prefix' => 'auth',
    'api_prefix' => 'api',
    'version_prefix' => 'v2',
    'auto_api_prefix' => true,
    'enable_versioning' => true,
],
```

**Resultado:** `/api/v2/auth/login`, `/api/v2/auth/register`, etc.

### 5. Rutas sin prefijo API pero con versión: `/v1/auth/`
```php
'routes' => [
    'prefix' => 'auth',
    'api_prefix' => null,
    'version_prefix' => 'v1',
    'auto_api_prefix' => false,
    'enable_versioning' => true,
],
```

**Resultado:** `/v1/auth/login`, `/v1/auth/register`, etc.

### 6. Rutas completamente personalizadas: `/my-api/auth/`
```php
'routes' => [
    'prefix' => 'auth',
    'api_prefix' => 'my-api',
    'version_prefix' => null,
    'auto_api_prefix' => true,
    'enable_versioning' => false,
],
```

**Resultado:** `/my-api/auth/login`, `/my-api/auth/register`, etc.

## Parámetros de Configuración

### `prefix` (string)
- **Descripción:** Prefijo base para todas las rutas
- **Valor por defecto:** `'auth'`
- **Ejemplo:** `'auth'`, `'authentication'`, `'auth-system'`

### `api_prefix` (string|null)
- **Descripción:** Prefijo de API que se agregará antes del prefijo base
- **Valor por defecto:** `'api'`
- **Ejemplo:** `'api'`, `'rest'`, `'v1'`, `null`

### `version_prefix` (string|null)
- **Descripción:** Prefijo de versión (solo se usa si `enable_versioning` es true)
- **Valor por defecto:** `null`
- **Ejemplo:** `'v1'`, `'v2'`, `'beta'`, `null`

### `auto_api_prefix` (boolean)
- **Descripción:** Si se debe agregar automáticamente el prefijo de API
- **Valor por defecto:** `true`
- **Comportamiento:** Si es `true` y `api_prefix` no es `null`, se agrega el prefijo

### `enable_versioning` (boolean)
- **Descripción:** Si se debe habilitar el versionado automático
- **Valor por defecto:** `false`
- **Comportamiento:** Si es `true` y `version_prefix` no es `null`, se agrega el prefijo de versión

### `middleware` (array)
- **Descripción:** Middleware que se aplica a todas las rutas
- **Valor por defecto:** `['api']`
- **Ejemplo:** `['api']`, `['web']`, `['api', 'cors']`

### `auth_middleware` (array)
- **Descripción:** Middleware que se aplica a las rutas protegidas
- **Valor por defecto:** `['auth:sanctum']`
- **Ejemplo:** `['auth:sanctum']`, `['auth:web']`, `['jwt.auth']`

## Rutas Disponibles

Una vez configurado el prefijo, las siguientes rutas estarán disponibles:

### Rutas Públicas
- `POST /{prefix}/login` - Iniciar sesión
- `POST /{prefix}/register` - Registrar usuario

### Rutas Protegidas
- `POST /{prefix}/logout` - Cerrar sesión
- `GET /{prefix}/me` - Obtener información del usuario actual
- `POST /{prefix}/refresh` - Renovar token

### Gestión de Usuarios
- `GET /{prefix}/users` - Listar usuarios
- `POST /{prefix}/users` - Crear usuario
- `GET /{prefix}/users/{user}` - Ver usuario
- `PUT /{prefix}/users/{user}` - Actualizar usuario
- `DELETE /{prefix}/users/{user}` - Eliminar usuario
- `POST /{prefix}/users/{user}/assign-roles` - Asignar roles
- `GET /{prefix}/users/{user}/roles` - Ver roles del usuario
- `GET /{prefix}/users/{user}/permissions` - Ver permisos del usuario

### Gestión de Roles
- `GET /{prefix}/roles` - Listar roles
- `POST /{prefix}/roles` - Crear rol
- `GET /{prefix}/roles/{role}` - Ver rol
- `PUT /{prefix}/roles/{role}` - Actualizar rol
- `DELETE /{prefix}/roles/{role}` - Eliminar rol
- `POST /{prefix}/roles/{role}/assign-permissions` - Asignar permisos
- `GET /{prefix}/roles/{role}/permissions` - Ver permisos del rol

### Gestión de Permisos
- `GET /{prefix}/permissions` - Listar permisos
- `POST /{prefix}/permissions` - Crear permiso
- `POST /{prefix}/permissions/bulk-create` - Crear múltiples permisos
- `GET /{prefix}/permissions/{permission}` - Ver permiso
- `PUT /{prefix}/permissions/{permission}` - Actualizar permiso
- `DELETE /{prefix}/permissions/{permission}` - Eliminar permiso

### Gestión de Módulos
- `GET /{prefix}/modules` - Listar módulos
- `POST /{prefix}/modules` - Crear módulo
- `GET /{prefix}/modules/{module}` - Ver módulo
- `PUT /{prefix}/modules/{module}` - Actualizar módulo
- `DELETE /{prefix}/modules/{module}` - Eliminar módulo
- `POST /{prefix}/modules/update-order` - Actualizar orden

### Gestión de Sucursales
- `GET /{prefix}/branches` - Listar sucursales
- `POST /{prefix}/branches` - Crear sucursal
- `GET /{prefix}/branches/{branch}` - Ver sucursal
- `PUT /{prefix}/branches/{branch}` - Actualizar sucursal
- `DELETE /{prefix}/branches/{branch}` - Eliminar sucursal

### Gestión de Departamentos
- `GET /{prefix}/departments` - Listar departamentos
- `POST /{prefix}/departments` - Crear departamento
- `GET /{prefix}/departments/{department}` - Ver departamento
- `PUT /{prefix}/departments/{department}` - Actualizar departamento
- `DELETE /{prefix}/departments/{department}` - Eliminar departamento

### Menú Dinámico
- `GET /{prefix}/menu` - Obtener menú
- `GET /{prefix}/menu/permissions` - Obtener permisos del menú
- `POST /{prefix}/menu/has-permission` - Verificar permiso
- `POST /{prefix}/menu/has-any-permission` - Verificar cualquier permiso
- `GET /{prefix}/menu/modules` - Obtener módulos del menú

## Migración desde Configuración Anterior

Si estás migrando desde la configuración anterior que usaba `'prefix' => 'api/v1/auth'`, puedes usar cualquiera de estas opciones:

### Opción 1: Mantener la misma estructura
```php
'routes' => [
    'prefix' => 'auth',
    'api_prefix' => 'api',
    'version_prefix' => 'v1',
    'auto_api_prefix' => true,
    'enable_versioning' => true,
],
```

### Opción 2: Simplificar a solo `/auth/`
```php
'routes' => [
    'prefix' => 'auth',
    'api_prefix' => null,
    'version_prefix' => null,
    'auto_api_prefix' => false,
    'enable_versioning' => false,
],
```

### Opción 3: Solo con prefijo API `/api/auth/`
```php
'routes' => [
    'prefix' => 'auth',
    'api_prefix' => 'api',
    'version_prefix' => null,
    'auto_api_prefix' => true,
    'enable_versioning' => false,
],
``` 