<?php

use Illuminate\Support\Facades\Route;
use Kaely\AuthPackage\Controllers\AuthController;
use Kaely\AuthPackage\Controllers\BranchController;
use Kaely\AuthPackage\Controllers\DepartmentController;
use Kaely\AuthPackage\Controllers\ModuleController;
use Kaely\AuthPackage\Controllers\PermissionController;
use Kaely\AuthPackage\Controllers\MenuController;
use Kaely\AuthPackage\Controllers\RoleController;
use Kaely\AuthPackage\Controllers\UserController;

// Obtener configuración con valores por defecto
$config = config('auth-package.routes', [
    'prefix' => 'api/v1/auth',
    'middleware' => ['api'],
    'auth_middleware' => ['auth:sanctum'],
]);

Route::prefix($config['prefix'])
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
        
        // Rutas de branches
        Route::prefix('branches')->group(function () {
            Route::get('/', [BranchController::class, 'index']);
            Route::post('/', [BranchController::class, 'store']);
            Route::get('/active', [BranchController::class, 'active']);
            Route::get('/{branch}', [BranchController::class, 'show']);
            Route::put('/{branch}', [BranchController::class, 'update']);
            Route::delete('/{branch}', [BranchController::class, 'destroy']);
        });
        
        // Rutas de departments
        Route::prefix('departments')->group(function () {
            Route::get('/', [DepartmentController::class, 'index']);
            Route::post('/', [DepartmentController::class, 'store']);
            Route::get('/active', [DepartmentController::class, 'active']);
            Route::get('/by-branch/{branchId}', [DepartmentController::class, 'byBranch']);
            Route::get('/{department}', [DepartmentController::class, 'show']);
            Route::put('/{department}', [DepartmentController::class, 'update']);
            Route::delete('/{department}', [DepartmentController::class, 'destroy']);
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
        
        // Rutas de usuarios
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::post('/', [UserController::class, 'store']);
            Route::get('/by-branch/{branchId}', [UserController::class, 'byBranch']);
            Route::get('/by-department/{departmentId}', [UserController::class, 'byDepartment']);
            Route::get('/{user}', [UserController::class, 'show']);
            Route::put('/{user}', [UserController::class, 'update']);
            Route::delete('/{user}', [UserController::class, 'destroy']);
            Route::post('/{user}/assign-roles', [UserController::class, 'assignRoles']);
            Route::get('/{user}/roles', [UserController::class, 'roles']);
            Route::get('/{user}/permissions', [UserController::class, 'permissions']);
        });
    });
}); 