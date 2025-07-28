<?php

use Illuminate\Support\Facades\Route;
use Kaely\AuthPackage\Controllers\AuthController;
use Kaely\AuthPackage\Controllers\ModuleController;
use Kaely\AuthPackage\Controllers\PermissionController;
use Kaely\AuthPackage\Controllers\MenuController;
use Kaely\AuthPackage\Controllers\RoleController;
use Kaely\AuthPackage\Controllers\RoleCategoryController;
use Kaely\AuthPackage\Controllers\PersonController;
use Kaely\AuthPackage\Controllers\UserController;
use Kaely\AuthPackage\Controllers\UserRoleController;

// Obtener configuración con valores por defecto
$config = config('auth-package.routes', [
    'prefix' => 'auth',
    'api_prefix' => 'api',
    'version_prefix' => null,
    'middleware' => ['api'],
    'auth_middleware' => ['auth:sanctum'],
    'enable_versioning' => false,
    'auto_api_prefix' => true,
]);

// Construir el prefijo completo de manera flexible
$prefixParts = [];

// Agregar prefijo de API si está habilitado
if ($config['auto_api_prefix'] && !empty($config['api_prefix'])) {
    $prefixParts[] = $config['api_prefix'];
}

// Agregar prefijo de versión si está habilitado
if ($config['enable_versioning'] && !empty($config['version_prefix'])) {
    $prefixParts[] = $config['version_prefix'];
}

// Agregar el prefijo base
$prefixParts[] = $config['prefix'];

// Construir el prefijo final
$finalPrefix = implode('/', array_filter($prefixParts));

Route::prefix($finalPrefix)
    ->middleware($config['middleware'])
    ->group(function () use ($config) {
        
    // Rutas públicas de autenticación
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    
    // Rutas protegidas de autenticación
    Route::middleware($config['auth_middleware'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        
        // Rutas de menú dinámico
        Route::prefix('menu')->group(function () {
            Route::get('/', [MenuController::class, 'index']);
            Route::get('/permissions', [MenuController::class, 'permissions']);
            Route::post('/has-permission', [MenuController::class, 'hasPermission']);
            Route::post('/has-any-permission', [MenuController::class, 'hasAnyPermission']);
            Route::get('/modules', [MenuController::class, 'modules']);
        });
        
        // Rutas de modules
        Route::prefix('modules')->group(function () {
            Route::get('/', [ModuleController::class, 'index']);
            Route::post('/', [ModuleController::class, 'store']);
            Route::get('/active', [ModuleController::class, 'active']);
            Route::post('/update-order', [ModuleController::class, 'updateOrder']);
            Route::get('/{module}', [ModuleController::class, 'show']);
            Route::put('/{module}', [ModuleController::class, 'update']);
            Route::delete('/{module}', [ModuleController::class, 'destroy']);
        });
        
        // Rutas de permissions
        Route::prefix('permissions')->group(function () {
            Route::get('/', [PermissionController::class, 'index']);
            Route::post('/', [PermissionController::class, 'store']);
            Route::post('/bulk-create', [PermissionController::class, 'bulkCreate']);
            Route::get('/active', [PermissionController::class, 'active']);
            Route::get('/by-module/{moduleId}', [PermissionController::class, 'byModule']);
            Route::get('/{permission}', [PermissionController::class, 'show']);
            Route::put('/{permission}', [PermissionController::class, 'update']);
            Route::delete('/{permission}', [PermissionController::class, 'destroy']);
        });
        
        // Rutas de categorías de roles
        Route::prefix('role-categories')->group(function () {
            Route::get('/', [RoleCategoryController::class, 'index']);
            Route::post('/', [RoleCategoryController::class, 'store']);
            Route::get('/active', [RoleCategoryController::class, 'active']);
            Route::get('/{roleCategory}', [RoleCategoryController::class, 'show']);
            Route::put('/{roleCategory}', [RoleCategoryController::class, 'update']);
            Route::delete('/{roleCategory}', [RoleCategoryController::class, 'destroy']);
            Route::get('/{roleCategory}/roles', [RoleCategoryController::class, 'roles']);
        });
        
        // Rutas de roles
        Route::prefix('roles')->group(function () {
            Route::get('/', [RoleController::class, 'index']);
            Route::post('/', [RoleController::class, 'store']);
            Route::get('/active', [RoleController::class, 'active']);
            Route::get('/{role}', [RoleController::class, 'show']);
            Route::put('/{role}', [RoleController::class, 'update']);
            Route::delete('/{role}', [RoleController::class, 'destroy']);
            Route::post('/{role}/assign-permissions', [RoleController::class, 'assignPermissions']);
            Route::get('/{role}/permissions', [RoleController::class, 'permissions']);
        });
        
        // Rutas de personas
        Route::prefix('people')->group(function () {
            Route::get('/', [PersonController::class, 'index']);
            Route::post('/', [PersonController::class, 'store']);
            Route::get('/statistics', [PersonController::class, 'statistics']);
            Route::get('/{person}', [PersonController::class, 'show']);
            Route::put('/{person}', [PersonController::class, 'update']);
            Route::delete('/{person}', [PersonController::class, 'destroy']);
        });
        
        // Rutas de usuarios
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::post('/', [UserController::class, 'store']);
            Route::get('/{user}', [UserController::class, 'show']);
            Route::put('/{user}', [UserController::class, 'update']);
            Route::delete('/{user}', [UserController::class, 'destroy']);
            Route::post('/{user}/assign-roles', [UserController::class, 'assignRoles']);
            Route::get('/{user}/roles', [UserController::class, 'roles']);
            Route::get('/{user}/permissions', [UserController::class, 'permissions']);
            Route::get('/{user}/person', [PersonController::class, 'byUser']);
            Route::post('/{user}/person', [PersonController::class, 'createOrUpdateForUser']);
        });
        
        // Rutas de asignaciones usuario-rol
         Route::prefix('user-roles')->group(function () {
             Route::get('/', [UserRoleController::class, 'index']);
             Route::post('/', [UserRoleController::class, 'store']);
             Route::delete('/', [UserRoleController::class, 'destroy']);
             Route::post('/bulk-assign', [UserRoleController::class, 'bulkAssign']);
             Route::post('/bulk-remove', [UserRoleController::class, 'bulkRemove']);
             Route::get('/statistics', [UserRoleController::class, 'statistics']);
             Route::get('/by-role/{role}', [UserRoleController::class, 'usersByRole']);
             Route::get('/by-user/{user}', [UserRoleController::class, 'rolesByUser']);
             Route::post('/sync/{user}', [UserRoleController::class, 'syncRoles']);
         });
    });
});