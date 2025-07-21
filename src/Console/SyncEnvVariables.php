<?php

namespace Kaely\AuthPackage\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class SyncEnvVariables extends Command
{
    protected $signature = 'auth-package:sync-env {--force : Sobrescribe variables existentes}';
    protected $description = 'Copia automáticamente las variables de env.example del paquete al .env del proyecto origen.';

    public function handle()
    {
        $filesystem = new Filesystem();
        // Buscar env.example en vendor y fallback a la raíz del paquete (útil en desarrollo)
        $envExamplePath = base_path('vendor/kaelytechnology/auth-package/env.example');
        if (!$filesystem->exists($envExamplePath)) {
            // fallback para desarrollo local del paquete
            $envExamplePath = __DIR__ . '/../../../env.example';
        }
        $envPath = base_path('.env');

        if (!$filesystem->exists($envExamplePath)) {
            $this->error('No se encontró el archivo env.example del paquete.');
            return 1;
        }
        if (!$filesystem->exists($envPath)) {
            $this->error('No se encontró el archivo .env en el proyecto.');
            return 1;
        }

        $envExample = collect(explode("\n", $filesystem->get($envExamplePath)));
        $env = collect(explode("\n", $filesystem->get($envPath)));

        $newVars = $envExample->filter(function ($line) {
            return preg_match('/^[A-Z0-9_]+=/i', $line);
        })->map(function ($line) {
            [$key, $value] = explode('=', $line, 2);
            return [trim($key) => $value];
        })->collapse();

        $existingKeys = $env->filter(function ($line) {
            return preg_match('/^[A-Z0-9_]+=/i', $line);
        })->map(function ($line) {
            return trim(explode('=', $line, 2)[0]);
        })->all();

        $added = 0;
        $copiedVars = [];
        foreach ($newVars as $key => $value) {
            if (!in_array($key, $existingKeys) || $this->option('force')) {
                $copiedVars[] = "$key=$value";
                $added++;
            }
        }

        if ($added > 0) {
            $env[] = "";
            $env[] = "# ====== KAELY AUTH PACKAGE ENV BEGIN ======";
            foreach ($copiedVars as $line) {
                $env[] = $line;
            }
            $env[] = "# ====== KAELY AUTH PACKAGE ENV END ======";
        }

        $filesystem->put($envPath, $env->implode("\n"));
        $this->info("Variables sincronizadas. Se agregaron o actualizaron $added variables.");
        return 0;
    }
}
