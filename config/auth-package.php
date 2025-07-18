<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication Package Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the authentication package.
    | You can customize these settings according to your needs.
    |
    */

    // Modelos del paquete
    'models' => [
        'user' => \Kaely\AuthPackage\Models\User::class,
        'role' => \Kaely\AuthPackage\Models\Role::class,
        'permission' => \Kaely\AuthPackage\Models\Permission::class,
        'role_category' => \Kaely\AuthPackage\Models\RoleCategory::class,
        'module' => \Kaely\AuthPackage\Models\Module::class,
        'person' => \Kaely\AuthPackage\Models\Person::class,
    ],

    // Configuración de autenticación
    'auth' => [
        'guard' => env('AUTH_GUARD', 'sanctum'),
        'provider' => env('AUTH_PROVIDER', 'users'),
        'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800), // 3 horas
    ],

    // Configuración de tokens
    'tokens' => [
        'expiration' => env('AUTH_TOKEN_EXPIRATION', 60 * 24 * 7), // 7 días
        'refresh_expiration' => env('AUTH_REFRESH_TOKEN_EXPIRATION', 60 * 24 * 30), // 30 días
    ],

    // Configuración de roles y permisos
    'roles' => [
        'cache_ttl' => env('AUTH_ROLES_CACHE_TTL', 3600), // 1 hora
        'default_role' => env('AUTH_DEFAULT_ROLE', 'user'),
    ],

    // Configuración de validación
    'validation' => [
        'password_min_length' => env('AUTH_PASSWORD_MIN_LENGTH', 8),
        'password_require_special' => env('AUTH_PASSWORD_REQUIRE_SPECIAL', false),
        'password_require_numbers' => env('AUTH_PASSWORD_REQUIRE_NUMBERS', true),
        'password_require_uppercase' => env('AUTH_PASSWORD_REQUIRE_UPPERCASE', true),
    ],

    // Configuración de rutas
    'routes' => [
        'prefix' => env('AUTH_ROUTES_PREFIX', 'auth'), // Prefijo base sin api/v1
        'api_prefix' => env('AUTH_ROUTES_API_PREFIX', 'api'), // Prefijo de API (opcional)
        'version_prefix' => env('AUTH_ROUTES_VERSION_PREFIX', null), // Prefijo de versión (opcional, ej: v1, v2)
        'middleware' => explode(',', env('AUTH_ROUTES_MIDDLEWARE', 'api')),
        'auth_middleware' => explode(',', env('AUTH_ROUTES_AUTH_MIDDLEWARE', 'auth:sanctum')),
        'enable_versioning' => env('AUTH_ROUTES_ENABLE_VERSIONING', false), // Habilitar versionado automático
        'auto_api_prefix' => env('AUTH_ROUTES_AUTO_API_PREFIX', true), // Agregar automáticamente el prefijo api
    ],

    // Configuración de respuestas
    'responses' => [
        'include_user_roles' => env('AUTH_INCLUDE_USER_ROLES', true),
        'include_user_permissions' => env('AUTH_INCLUDE_USER_PERMISSIONS', true),
    ],
]; 