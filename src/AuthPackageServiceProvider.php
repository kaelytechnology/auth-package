<?php

namespace Kaely\AuthPackage;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;

class AuthPackageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registrar configuraciones
        $this->mergeConfigFrom(__DIR__.'/../config/auth-package.php', 'auth-package');
        
        // Registrar middleware
        $this->app['router']->aliasMiddleware('auth.role', \Kaely\AuthPackage\Middleware\CheckRole::class);
        $this->app['router']->aliasMiddleware('auth.permission', \Kaely\AuthPackage\Middleware\CheckPermission::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publicar configuraciones
        $this->publishes([
            __DIR__.'/../config/auth-package.php' => config_path('auth-package.php'),
        ], 'auth-package-config');

        // Publicar migraciones (excluyendo las que Laravel crea por defecto)
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'auth-package-migrations');

        // Publicar seeders
        $this->publishes([
            __DIR__.'/../database/seeders' => database_path('seeders'),
        ], 'auth-package-seeders');

        // Cargar rutas
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        // Cargar vistas
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'auth-package');

        // Cargar migraciones
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Registrar comandos
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Kaely\AuthPackage\Console\InstallAuthPackage::class,
            ]);
        }
    }
} 