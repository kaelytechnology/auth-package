<?php

use Illuminate\Support\Facades\Route;
use Kaely\AuthPackage\Controllers\AuthController;

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
    });
}); 