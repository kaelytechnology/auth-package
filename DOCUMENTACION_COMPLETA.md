# Kaely Auth Package - Documentación Completa

## 📋 Índice

1. [Introducción](#introducción)
2. [Instalación](#instalación)
3. [Configuración](#configuración)
4. [Rutas de Autenticación](#rutas-de-autenticación)
5. [Rutas de Menú Dinámico](#rutas-de-menú-dinámico)
6. [Rutas de Gestión de Usuarios](#rutas-de-gestión-de-usuarios)
7. [Rutas de Gestión de Roles](#rutas-de-gestión-de-roles)
8. [Rutas de Gestión de Permisos](#rutas-de-gestión-de-permisos)
9. [Rutas de Gestión de Módulos](#rutas-de-gestión-de-módulos)
10. [Middleware](#middleware)
11. [Modelos](#modelos)
12. [Testing](#testing)
13. [Solución de Problemas](#solución-de-problemas)

## 🚀 Introducción

El **Kaely Auth Package** es un paquete completo de autenticación y autorización para Laravel que proporciona:

- ✅ Autenticación con Laravel Sanctum
- ✅ Sistema de roles y permisos
- ✅ Gestión de usuarios
- ✅ Gestión de módulos
- ✅ Menú dinámico basado en permisos
- ✅ API RESTful completa
- ✅ Configuración flexible con variables de entorno
- ✅ Tests completos incluidos

## 📦 Instalación

### 1. Instalar el paquete

```bash
composer require kaely/auth-package
```

### 2. Publicar configuraciones

```bash
php artisan vendor:publish --tag=auth-package-config
```

### 3. Publicar migraciones

```bash
php artisan vendor:publish --tag=auth-package-migrations
```

### 4. Ejecutar migraciones

```bash
php artisan migrate
```

### 5. Ejecutar seeders

```bash
# Opción 1: Publicar y ejecutar
php artisan vendor:publish --provider="Kaely\AuthPackage\AuthPackageServiceProvider" --tag=auth-package-seeders
php artisan db:seed --class=AuthPackageSeeder

# Opción 2: Ejecutar directamente
php artisan db:seed --class="Kaely\AuthPackage\Database\Seeders\AuthPackageSeeder"
```

### 6. Instalación automática (recomendado)

```bash
php artisan auth-package:install
```

## ⚙️ Configuración

### Variables de Entorno

Configura estas variables en tu archivo `.env`:

```env
# Configuración de Autenticación
AUTH_GUARD=sanctum
AUTH_PROVIDER=users
AUTH_PASSWORD_TIMEOUT=10800

# Configuración de Tokens
AUTH_TOKEN_EXPIRATION=604800
AUTH_REFRESH_TOKEN_EXPIRATION=2592000

# Configuración de Roles y Permisos
AUTH_ROLES_CACHE_TTL=3600
AUTH_DEFAULT_ROLE=user

# Validación de Contraseñas
AUTH_PASSWORD_MIN_LENGTH=8
AUTH_PASSWORD_REQUIRE_SPECIAL=false
AUTH_PASSWORD_REQUIRE_NUMBERS=true
AUTH_PASSWORD_REQUIRE_UPPERCASE=true

# Configuración de Rutas
AUTH_ROUTES_PREFIX=auth
AUTH_ROUTES_API_PREFIX=api
AUTH_ROUTES_VERSION_PREFIX=v1
AUTH_ROUTES_MIDDLEWARE=api
AUTH_ROUTES_AUTH_MIDDLEWARE=auth:sanctum
AUTH_ROUTES_ENABLE_VERSIONING=true
AUTH_ROUTES_AUTO_API_PREFIX=true

# Configuración de Respuestas
AUTH_INCLUDE_USER_ROLES=true
AUTH_INCLUDE_USER_PERMISSIONS=true
```

## 🔐 Rutas de Autenticación

### Registro de Usuario

**POST** `/{prefix}/register`

Registra un nuevo usuario en el sistema.

**Parámetros:**
```json
{
    "name": "Juan Pérez",
    "email": "juan@ejemplo.com",
    "password": "contraseña123",
    "password_confirmation": "contraseña123"
}
```

**Respuesta exitosa (201):**
```json
{
    "message": "Usuario registrado exitosamente",
    "data": {
        "id": 1,
        "name": "Juan Pérez",
        "email": "juan@ejemplo.com",
        "is_active": true,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

### Inicio de Sesión

**POST** `/{prefix}/login`

Inicia sesión y devuelve un token de acceso.

**Parámetros:**
```json
{
    "email": "juan@ejemplo.com",
    "password": "contraseña123"
}
```

**Respuesta exitosa (200):**
```json
{
    "message": "Inicio de sesión exitoso",
    "data": {
        "user": {
            "id": 1,
            "name": "Juan Pérez",
            "email": "juan@ejemplo.com",
            "is_active": true,
            "created_at": "2024-01-01T00:00:00.000000Z",
            "updated_at": "2024-01-01T00:00:00.000000Z"
        },
        "token": "1|abc123def456..."
    }
}
```

### Cerrar Sesión

**POST** `/{prefix}/logout`

Cierra la sesión del usuario actual.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Respuesta exitosa (200):**
```json
{
    "message": "Sesión cerrada exitosamente"
}
```

### Obtener Usuario Actual

**GET** `/{prefix}/me`

Obtiene la información del usuario autenticado.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Respuesta exitosa (200):**
```json
{
    "message": "Información del usuario obtenida",
    "data": {
        "id": 1,
        "name": "Juan Pérez",
        "email": "juan@ejemplo.com",
        "is_active": true,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

### Refrescar Token

**POST** `/{prefix}/refresh`

Refresca el token de acceso del usuario.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Respuesta exitosa (200):**
```json
{
    "message": "Token refrescado exitosamente",
    "data": {
        "token": "2|xyz789abc123..."
    }
}
```

## 🍽️ Rutas de Menú Dinámico

### Obtener Menú Dinámico

**GET** `/{prefix}/menu`

Obtiene el menú dinámico basado en los permisos del usuario.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Respuesta exitosa (200):**
```json
{
    "data": [
        {
            "id": 1,
            "name": "Autenticación",
            "slug": "auth",
            "description": "Módulo de autenticación y autorización",
            "icon": "fas fa-shield-alt",
            "route": "/auth",
            "order": 1,
            "is_active": true,
            "permissions": [
                {
                    "id": 1,
                    "name": "Login",
                    "slug": "auth.login"
                }
            ]
        }
    ]
}
```

### Obtener Permisos del Usuario

**GET** `/{prefix}/menu/permissions`

Obtiene todos los permisos del usuario autenticado.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Respuesta exitosa (200):**
```json
{
    "data": [
        {
            "id": 1,
            "name": "Login",
            "slug": "auth.login",
            "description": "Puede iniciar sesión en el sistema"
        }
    ]
}
```

### Verificar Permiso Específico

**POST** `/{prefix}/menu/has-permission`

Verifica si el usuario tiene un permiso específico.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Parámetros:**
```json
{
    "permission": "auth.login"
}
```

**Respuesta exitosa (200):**
```json
{
    "data": {
        "has_permission": true,
        "permission": "auth.login"
    }
}
```

### Verificar Múltiples Permisos

**POST** `/{prefix}/menu/has-any-permission`

Verifica si el usuario tiene al menos uno de los permisos especificados.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Parámetros:**
```json
{
    "permissions": ["auth.login", "users.create", "roles.view"]
}
```

**Respuesta exitosa (200):**
```json
{
    "data": {
        "has_permission": true,
        "permissions": ["auth.login", "users.create", "roles.view"],
        "matched_permissions": ["auth.login", "users.create"]
    }
}
```

### Obtener Módulos del Usuario

**GET** `/{prefix}/menu/modules`

Obtiene los módulos a los que tiene acceso el usuario.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Respuesta exitosa (200):**
```json
{
    "data": [
        {
            "id": 1,
            "name": "Autenticación",
            "slug": "auth",
            "description": "Módulo de autenticación",
            "icon": "fas fa-shield-alt",
            "route": "/auth",
            "order": 1,
            "is_active": true
        }
    ]
}
```

## 👥 Rutas de Gestión de Usuarios

### Listar Usuarios

**GET** `/{prefix}/users`

Obtiene una lista paginada de usuarios.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Parámetros de consulta:**
- `search`: Buscar por nombre o email
- `status`: Filtrar por estado (true/false)
- `sort_by`: Campo de ordenamiento (default: name)
- `sort_order`: Dirección de ordenamiento (asc/desc)
- `per_page`: Elementos por página (default: 15)

**Respuesta exitosa (200):**
```json
{
    "data": {
        "data": [
            {
                "id": 1,
                "name": "Juan Pérez",
                "email": "juan@ejemplo.com",
                "is_active": true,
                "created_at": "2024-01-01T00:00:00.000000Z",
                "updated_at": "2024-01-01T00:00:00.000000Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "last_page": 1,
            "per_page": 15,
            "total": 1
        }
    }
}
```

### Crear Usuario

**POST** `/{prefix}/users`

Crea un nuevo usuario.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Parámetros:**
```json
{
    "name": "María García",
    "email": "maria@ejemplo.com",
    "password": "contraseña123",
    "is_active": true,
    "roles": [1, 2]
}
```

**Respuesta exitosa (201):**
```json
{
    "message": "Usuario creado exitosamente",
    "data": {
        "id": 2,
        "name": "María García",
        "email": "maria@ejemplo.com",
        "is_active": true,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

### Obtener Usuario Específico

**GET** `/{prefix}/users/{id}`

Obtiene la información de un usuario específico.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Respuesta exitosa (200):**
```json
{
    "data": {
        "id": 1,
        "name": "Juan Pérez",
        "email": "juan@ejemplo.com",
        "is_active": true,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

### Actualizar Usuario

**PUT** `/{prefix}/users/{id}`

Actualiza la información de un usuario.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Parámetros:**
```json
{
    "name": "Juan Carlos Pérez",
    "email": "juancarlos@ejemplo.com",
    "is_active": false
}
```

**Respuesta exitosa (200):**
```json
{
    "message": "Usuario actualizado exitosamente",
    "data": {
        "id": 1,
        "name": "Juan Carlos Pérez",
        "email": "juancarlos@ejemplo.com",
        "is_active": false,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

### Eliminar Usuario

**DELETE** `/{prefix}/users/{id}`

Elimina un usuario (soft delete).

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Respuesta exitosa (200):**
```json
{
    "message": "Usuario eliminado exitosamente"
}
```

### Asignar Roles a Usuario

**POST** `/{prefix}/users/{id}/assign-roles`

Asigna roles a un usuario específico.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Parámetros:**
```json
{
    "roles": [1, 2, 3]
}
```

**Respuesta exitosa (200):**
```json
{
    "message": "Roles asignados exitosamente",
    "data": {
        "id": 1,
        "name": "Juan Pérez",
        "email": "juan@ejemplo.com",
        "is_active": true
    }
}
```

### Obtener Roles del Usuario

**GET** `/{prefix}/users/{id}/roles`

Obtiene los roles asignados a un usuario.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Respuesta exitosa (200):**
```json
{
    "user": {
        "id": 1,
        "name": "Juan Pérez",
        "email": "juan@ejemplo.com"
    },
    "roles": [
        {
            "id": 1,
            "name": "Super Admin",
            "slug": "super-admin",
            "description": "Super administrador",
            "permissions": [
                {
                    "id": 1,
                    "name": "Login",
                    "slug": "auth.login"
                }
            ]
        }
    ]
}
```

### Obtener Permisos del Usuario

**GET** `/{prefix}/users/{id}/permissions`

Obtiene todos los permisos que tiene un usuario a través de sus roles.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Respuesta exitosa (200):**
```json
{
    "user": {
        "id": 1,
        "name": "Juan Pérez",
        "email": "juan@ejemplo.com"
    },
    "permissions": [
        {
            "id": 1,
            "name": "Login",
            "slug": "auth.login",
            "description": "Puede iniciar sesión"
        }
    ]
}
```

## 🎭 Rutas de Gestión de Roles

### Listar Roles

**GET** `/{prefix}/roles`

Obtiene una lista paginada de roles.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Parámetros de consulta:**
- `search`: Buscar por nombre o slug
- `status`: Filtrar por estado (true/false)
- `sort_by`: Campo de ordenamiento (default: name)
- `sort_order`: Dirección de ordenamiento (asc/desc)
- `per_page`: Elementos por página (default: 15)

**Respuesta exitosa (200):**
```json
{
    "data": {
        "data": [
            {
                "id": 1,
                "name": "Super Admin",
                "slug": "super-admin",
                "description": "Super administrador",
                "status": true,
                "created_at": "2024-01-01T00:00:00.000000Z",
                "updated_at": "2024-01-01T00:00:00.000000Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "last_page": 1,
            "per_page": 15,
            "total": 1
        }
    }
}
```

### Crear Rol

**POST** `/{prefix}/roles`

Crea un nuevo rol.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Parámetros:**
```json
{
    "name": "Editor",
    "slug": "editor",
    "description": "Rol de editor",
    "role_category_id": 1,
    "status": true
}
```

**Respuesta exitosa (201):**
```json
{
    "message": "Rol creado exitosamente",
    "data": {
        "id": 2,
        "name": "Editor",
        "slug": "editor",
        "description": "Rol de editor",
        "status": true,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

### Obtener Rol Específico

**GET** `/{prefix}/roles/{id}`

Obtiene la información de un rol específico.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Respuesta exitosa (200):**
```json
{
    "data": {
        "id": 1,
        "name": "Super Admin",
        "slug": "super-admin",
        "description": "Super administrador",
        "status": true,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

### Actualizar Rol

**PUT** `/{prefix}/roles/{id}`

Actualiza la información de un rol.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Parámetros:**
```json
{
    "name": "Administrador Senior",
    "slug": "admin-senior",
    "description": "Administrador con permisos extendidos",
    "status": false
}
```

**Respuesta exitosa (200):**
```json
{
    "message": "Rol actualizado exitosamente",
    "data": {
        "id": 1,
        "name": "Administrador Senior",
        "slug": "admin-senior",
        "description": "Administrador con permisos extendidos",
        "status": false,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

### Eliminar Rol

**DELETE** `/{prefix}/roles/{id}`

Elimina un rol (soft delete).

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Respuesta exitosa (200):**
```json
{
    "message": "Rol eliminado exitosamente"
}
```

### Asignar Permisos a Rol

**POST** `/{prefix}/roles/{id}/assign-permissions`

Asigna permisos a un rol específico.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Parámetros:**
```json
{
    "permissions": [1, 2, 3, 4]
}
```

**Respuesta exitosa (200):**
```json
{
    "message": "Permisos asignados exitosamente"
}
```

### Obtener Permisos del Rol

**GET** `/{prefix}/roles/{id}/permissions`

Obtiene los permisos asignados a un rol.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Respuesta exitosa (200):**
```json
{
    "role": {
        "id": 1,
        "name": "Super Admin",
        "slug": "super-admin",
        "description": "Super administrador"
    },
    "permissions": [
        {
            "id": 1,
            "name": "Login",
            "slug": "auth.login",
            "description": "Puede iniciar sesión"
        }
    ]
}
```

### Obtener Roles Activos

**GET** `/{prefix}/roles/active`

Obtiene solo los roles activos.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Respuesta exitosa (200):**
```json
{
    "data": [
        {
            "id": 1,
            "name": "Super Admin",
            "slug": "super-admin",
            "description": "Super administrador",
            "status": true
        }
    ]
}
```

## 🔑 Rutas de Gestión de Permisos

### Listar Permisos

**GET** `/{prefix}/permissions`

Obtiene una lista paginada de permisos.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Parámetros de consulta:**
- `search`: Buscar por nombre o slug
- `status`: Filtrar por estado (true/false)
- `module_id`: Filtrar por módulo
- `sort_by`: Campo de ordenamiento (default: name)
- `sort_order`: Dirección de ordenamiento (asc/desc)
- `per_page`: Elementos por página (default: 15)

**Respuesta exitosa (200):**
```json
{
    "data": {
        "data": [
            {
                "id": 1,
                "name": "Login",
                "slug": "auth.login",
                "description": "Puede iniciar sesión",
                "status": true,
                "created_at": "2024-01-01T00:00:00.000000Z",
                "updated_at": "2024-01-01T00:00:00.000000Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "last_page": 1,
            "per_page": 15,
            "total": 1
        }
    }
}
```

### Crear Permiso

**POST** `/{prefix}/permissions`

Crea un nuevo permiso.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Parámetros:**
```json
{
    "name": "Crear Usuarios",
    "slug": "users.create",
    "description": "Puede crear nuevos usuarios",
    "module_id": 2,
    "status": true
}
```

**Respuesta exitosa (201):**
```json
{
    "message": "Permiso creado exitosamente",
    "data": {
        "id": 2,
        "name": "Crear Usuarios",
        "slug": "users.create",
        "description": "Puede crear nuevos usuarios",
        "status": true,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

### Obtener Permiso Específico

**GET** `/{prefix}/permissions/{id}`

Obtiene la información de un permiso específico.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Respuesta exitosa (200):**
```json
{
    "data": {
        "id": 1,
        "name": "Login",
        "slug": "auth.login",
        "description": "Puede iniciar sesión",
        "status": true,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

### Actualizar Permiso

**PUT** `/{prefix}/permissions/{id}`

Actualiza la información de un permiso.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Parámetros:**
```json
{
    "name": "Iniciar Sesión",
    "slug": "auth.login",
    "description": "Permite al usuario iniciar sesión en el sistema",
    "status": false
}
```

**Respuesta exitosa (200):**
```json
{
    "message": "Permiso actualizado exitosamente",
    "data": {
        "id": 1,
        "name": "Iniciar Sesión",
        "slug": "auth.login",
        "description": "Permite al usuario iniciar sesión en el sistema",
        "status": false,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

### Eliminar Permiso

**DELETE** `/{prefix}/permissions/{id}`

Elimina un permiso (soft delete).

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Respuesta exitosa (200):**
```json
{
    "message": "Permiso eliminado exitosamente"
}
```

### Obtener Permisos Activos

**GET** `/{prefix}/permissions/active`

Obtiene solo los permisos activos.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Respuesta exitosa (200):**
```json
{
    "data": [
        {
            "id": 1,
            "name": "Login",
            "slug": "auth.login",
            "description": "Puede iniciar sesión",
            "status": true
        }
    ]
}
```

### Obtener Permisos por Módulo

**GET** `/{prefix}/permissions/by-module/{moduleId}`

Obtiene los permisos de un módulo específico.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Respuesta exitosa (200):**
```json
{
    "data": [
        {
            "id": 1,
            "name": "Login",
            "slug": "auth.login",
            "description": "Puede iniciar sesión",
            "status": true
        }
    ]
}
```

### Crear Permisos en Lote

**POST** `/{prefix}/permissions/bulk-create`

Crea múltiples permisos para un módulo.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Parámetros:**
```json
{
    "module_id": 2,
    "permissions": [
        {
            "name": "Ver Usuarios",
            "slug": "users.view",
            "description": "Puede ver la lista de usuarios"
        },
        {
            "name": "Crear Usuarios",
            "slug": "users.create",
            "description": "Puede crear nuevos usuarios"
        },
        {
            "name": "Editar Usuarios",
            "slug": "users.edit",
            "description": "Puede editar usuarios existentes"
        }
    ]
}
```

**Respuesta exitosa (201):**
```json
{
    "message": "Permisos creados exitosamente",
    "data": [
        {
            "id": 2,
            "name": "Ver Usuarios",
            "slug": "users.view",
            "description": "Puede ver la lista de usuarios"
        },
        {
            "id": 3,
            "name": "Crear Usuarios",
            "slug": "users.create",
            "description": "Puede crear nuevos usuarios"
        },
        {
            "id": 4,
            "name": "Editar Usuarios",
            "slug": "users.edit",
            "description": "Puede editar usuarios existentes"
        }
    ]
}
```

## 📦 Rutas de Gestión de Módulos

### Listar Módulos

**GET** `/{prefix}/modules`

Obtiene una lista paginada de módulos.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Parámetros de consulta:**
- `search`: Buscar por nombre o slug
- `status`: Filtrar por estado (true/false)
- `sort_by`: Campo de ordenamiento (default: name)
- `sort_order`: Dirección de ordenamiento (asc/desc)
- `per_page`: Elementos por página (default: 15)

**Respuesta exitosa (200):**
```json
{
    "data": {
        "data": [
            {
                "id": 1,
                "name": "Autenticación",
                "slug": "auth",
                "description": "Módulo de autenticación",
                "icon": "fas fa-shield-alt",
                "route": "/auth",
                "order": 1,
                "is_active": true,
                "created_at": "2024-01-01T00:00:00.000000Z",
                "updated_at": "2024-01-01T00:00:00.000000Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "last_page": 1,
            "per_page": 15,
            "total": 1
        }
    }
}
```

### Crear Módulo

**POST** `/{prefix}/modules`

Crea un nuevo módulo.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Parámetros:**
```json
{
    "name": "Reportes",
    "slug": "reports",
    "description": "Módulo de reportes",
    "icon": "fas fa-chart-bar",
    "route": "/reports",
    "order": 5,
    "is_active": true
}
```

**Respuesta exitosa (201):**
```json
{
    "message": "Módulo creado exitosamente",
    "data": {
        "id": 2,
        "name": "Reportes",
        "slug": "reports",
        "description": "Módulo de reportes",
        "icon": "fas fa-chart-bar",
        "route": "/reports",
        "order": 5,
        "is_active": true,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

### Obtener Módulo Específico

**GET** `/{prefix}/modules/{id}`

Obtiene la información de un módulo específico.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Respuesta exitosa (200):**
```json
{
    "data": {
        "id": 1,
        "name": "Autenticación",
        "slug": "auth",
        "description": "Módulo de autenticación",
        "icon": "fas fa-shield-alt",
        "route": "/auth",
        "order": 1,
        "is_active": true,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

### Actualizar Módulo

**PUT** `/{prefix}/modules/{id}`

Actualiza la información de un módulo.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Parámetros:**
```json
{
    "name": "Autenticación y Autorización",
    "slug": "auth",
    "description": "Módulo completo de autenticación y autorización",
    "icon": "fas fa-shield-alt",
    "route": "/auth",
    "order": 1,
    "is_active": false
}
```

**Respuesta exitosa (200):**
```json
{
    "message": "Módulo actualizado exitosamente",
    "data": {
        "id": 1,
        "name": "Autenticación y Autorización",
        "slug": "auth",
        "description": "Módulo completo de autenticación y autorización",
        "icon": "fas fa-shield-alt",
        "route": "/auth",
        "order": 1,
        "is_active": false,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

### Eliminar Módulo

**DELETE** `/{prefix}/modules/{id}`

Elimina un módulo (soft delete).

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Respuesta exitosa (200):**
```json
{
    "message": "Módulo eliminado exitosamente"
}
```

### Obtener Módulos Activos

**GET** `/{prefix}/modules/active`

Obtiene solo los módulos activos.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Respuesta exitosa (200):**
```json
{
    "data": [
        {
            "id": 1,
            "name": "Autenticación",
            "slug": "auth",
            "description": "Módulo de autenticación",
            "icon": "fas fa-shield-alt",
            "route": "/auth",
            "order": 1,
            "is_active": true
        }
    ]
}
```

### Actualizar Orden de Módulos

**POST** `/{prefix}/modules/update-order`

Actualiza el orden de los módulos.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Parámetros:**
```json
{
    "modules": [
        {"id": 1, "order": 1},
        {"id": 2, "order": 2},
        {"id": 3, "order": 3}
    ]
}
```

**Respuesta exitosa (200):**
```json
{
    "message": "Orden de módulos actualizado exitosamente"
}
```

## 🛡️ Middleware

El paquete incluye middleware para verificar roles y permisos:

### Verificar Rol

```php
// Verificar rol específico
Route::middleware('auth.role:admin')->group(function () {
    // Rutas que requieren rol admin
});

// Verificar múltiples roles
Route::middleware('auth.role:admin,editor')->group(function () {
    // Rutas que requieren rol admin o editor
});
```

### Verificar Permiso

```php
// Verificar permiso específico
Route::middleware('auth.permission:users.create')->group(function () {
    // Rutas que requieren permiso users.create
});

// Verificar múltiples permisos
Route::middleware('auth.permission:users.create,users.edit')->group(function () {
    // Rutas que requieren permiso users.create o users.edit
});
```

## 🏗️ Modelos

### User

```php
use Kaely\AuthPackage\Models\User;

$user = User::find(1);

// Verificar rol
if ($user->hasRole('admin')) {
    // Usuario tiene rol admin
}

// Verificar permiso
if ($user->hasPermission('users.create')) {
    // Usuario tiene permiso para crear usuarios
}

// Verificar múltiples permisos
if ($user->hasAnyPermission(['users.create', 'users.edit'])) {
    // Usuario tiene al menos uno de los permisos
}

// Obtener roles
$roles = $user->roles;

// Obtener permisos
$permissions = $user->getAllPermissions();

// Activar/Desactivar usuario
$user->activate();
$user->deactivate();
```

### Role

```php
use Kaely\AuthPackage\Models\Role;

$role = Role::find(1);

// Verificar permiso
if ($role->hasPermission('users.create')) {
    // El rol tiene permiso para crear usuarios
}

// Asignar permisos
$role->permissions()->attach([1, 2, 3]);

// Sincronizar permisos
$role->permissions()->sync([1, 2, 3]);

// Obtener permisos
$permissions = $role->permissions;
```

### Permission

```php
use Kaely\AuthPackage\Models\Permission;

$permission = Permission::find(1);

// Obtener roles que tienen este permiso
$roles = $permission->roles;

// Obtener módulo al que pertenece
$module = $permission->module;
```

### Module

```php
use Kaely\AuthPackage\Models\Module;

$module = Module::find(1);

// Obtener permisos del módulo
$permissions = $module->permissions;

// Verificar si está activo
if ($module->is_active) {
    // Módulo está activo
}
```

## 🧪 Testing

### Ejecutar Tests

```bash
# Ejecutar todos los tests
php artisan test

# Ejecutar tests específicos
php artisan test --filter=AuthControllerTest
php artisan test --filter=UserControllerTest
php artisan test --filter=RoleControllerTest
php artisan test --filter=MenuControllerTest
```

### Tests Incluidos

- **AuthControllerTest**: Tests de autenticación (login, register, logout, etc.)
- **UserControllerTest**: Tests de gestión de usuarios (CRUD, roles, permisos)
- **RoleControllerTest**: Tests de gestión de roles (CRUD, permisos)
- **MenuControllerTest**: Tests de menú dinámico y verificación de permisos

### Configuración de Tests

Los tests utilizan SQLite en memoria para mayor velocidad y aislamiento. La configuración se maneja automáticamente en el `TestCase` base.

## 🔧 Solución de Problemas

### Problemas Comunes

#### Error de Migración

Si encuentras errores de migración:

```bash
php artisan auth-package:fix-migrations
```

#### Error de Seeder

Si el seeder no se ejecuta:

```bash
# Opción 1: Publicar y ejecutar
php artisan vendor:publish --provider="Kaely\AuthPackage\AuthPackageServiceProvider" --tag=auth-package-seeders
php artisan db:seed --class=AuthPackageSeeder

# Opción 2: Ejecutar directamente
php artisan db:seed --class="Kaely\AuthPackage\Database\Seeders\AuthPackageSeeder"
```

#### Error de Rutas

Si las rutas no funcionan, verifica la configuración:

```env
AUTH_ROUTES_PREFIX=auth
AUTH_ROUTES_API_PREFIX=api
AUTH_ROUTES_VERSION_PREFIX=v1
AUTH_ROUTES_ENABLE_VERSIONING=true
AUTH_ROUTES_AUTO_API_PREFIX=true
```

### Credenciales por Defecto

Después de ejecutar los seeders, se crea un usuario administrador:

- **Email:** admin@example.com
- **Password:** password

### Logs

Los logs del paquete se guardan en:
```
storage/logs/laravel.log
```

## 📞 Soporte

Para soporte técnico o reportar problemas:

1. Revisa la documentación completa
2. Consulta [TROUBLESHOOTING.md](TROUBLESHOOTING.md)
3. Abre un issue en el repositorio de GitHub
4. Contacta al equipo de desarrollo

---

**Versión:** 1.6.0  
**Laravel:** 12.x  
**PHP:** 8.2+  
**Licencia:** MIT 