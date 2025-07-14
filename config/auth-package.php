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
        'guard' => 'sanctum',
        'provider' => 'users',
        'password_timeout' => 10800, // 3 horas
    ],

    // Configuración de tokens
    'tokens' => [
        'expiration' => 60 * 24 * 7, // 7 días
        'refresh_expiration' => 60 * 24 * 30, // 30 días
    ],

    // Configuración de roles y permisos
    'roles' => [
        'cache_ttl' => 3600, // 1 hora
        'default_role' => 'user',
    ],

    // Configuración de validación
    'validation' => [
        'password_min_length' => 8,
        'password_require_special' => false,
        'password_require_numbers' => true,
        'password_require_uppercase' => true,
    ],

    // Configuración de rutas
    'routes' => [
        'prefix' => 'api/v1/auth',
        'middleware' => ['api'],
        'auth_middleware' => ['auth:sanctum'],
    ],

    // Configuración de respuestas
    'responses' => [
        'include_user_roles' => true,
        'include_user_permissions' => true,
        'include_user_branches' => true,
        'include_user_departments' => true,
    ],
]; 