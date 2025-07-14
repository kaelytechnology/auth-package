<?php

use Illuminate\Support\Facades\Route;
use Kaely\AuthPackage\Controllers\AuthController;

$config = config('auth-package.routes');

Route::prefix($config['prefix'])
    ->middleware($config['middleware'])
    ->group(function () {
        
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