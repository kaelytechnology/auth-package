<?php

namespace Kaely\AuthPackage\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallAuthPackage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth-package:install {--force : Force the installation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the authentication package';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Installing Authentication Package...');

        // Publicar configuraciones
        $this->info('Publishing configuration files...');
        $this->callSilent('vendor:publish', [
            '--tag' => 'auth-package-config',
            '--force' => $this->option('force')
        ]);

        // Publicar migraciones
        $this->info('Publishing migrations...');
        $this->callSilent('vendor:publish', [
            '--tag' => 'auth-package-migrations',
            '--force' => $this->option('force')
        ]);

        // Publicar seeders
        $this->info('Publishing seeders...');
        $this->callSilent('vendor:publish', [
            '--tag' => 'auth-package-seeders',
            '--force' => $this->option('force')
        ]);

        // Verificar si las migraciones básicas de Laravel existen
        $this->info('Checking Laravel migrations...');
        
        // Verificar si la tabla users existe
        if (!\Schema::hasTable('users')) {
            $this->warn('Users table not found. Running Laravel default migrations first...');
            $this->callSilent('migrate', ['--path' => 'database/migrations']);
        }

        // Verificar si la tabla personal_access_tokens existe (Sanctum)
        if (!\Schema::hasTable('personal_access_tokens')) {
            $this->warn('Personal access tokens table not found. Installing Sanctum...');
            $this->callSilent('vendor:publish', [
                '--provider' => 'Laravel\Sanctum\SanctumServiceProvider'
            ]);
            $this->callSilent('migrate');
        }

        // Ejecutar migraciones del paquete
        $this->info('Running package migrations...');
        $this->callSilent('migrate');

        // Ejecutar seeders
        $this->info('Running seeders...');
        try {
            // Intentar ejecutar el seeder publicado primero
            $this->callSilent('db:seed', [
                '--class' => 'AuthPackageSeeder'
            ]);
        } catch (\Exception $e) {
            // Si falla, intentar ejecutar desde vendor
            $this->warn('Published seeder not found, trying vendor seeder...');
            $this->callSilent('db:seed', [
                '--class' => 'Kaely\AuthPackage\Database\Seeders\AuthPackageSeeder'
            ]);
        }

        $this->info('Authentication Package installed successfully!');
        $this->info('');
        $this->info('Default admin credentials:');
        $this->info('Email: admin@example.com');
        $this->info('Password: password');
        $this->info('');
        $this->info('Available routes:');
        $this->info('- POST /{prefix}/login');
        $this->info('- POST /{prefix}/register');
        $this->info('- POST /{prefix}/logout (requires auth)');
        $this->info('- GET /{prefix}/me (requires auth)');
        $this->info('- POST /{prefix}/refresh (requires auth)');
        $this->info('');
        $this->info('Note: Routes prefix is configurable in config/auth-package.php');
        $this->info('');
        $this->info('Note: This package extends Laravel\'s default users table with additional fields.');

        return Command::SUCCESS;
    }
} 