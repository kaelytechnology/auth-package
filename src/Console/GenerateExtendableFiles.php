<?php

namespace Kaely\AuthPackage\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateExtendableFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth-package:generate-extendable {--force : Overwrite existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate extendable models and controllers for the auth package';

    public function handle(): int
    {
        $this->info('Generating extendable models and controllers...');

        $this->createDirectories();
        $this->generateExtendableModels();
        $this->generateExtendableControllers();

        $this->info('Extendable files generated successfully!');
        $this->info('');
        $this->info('Generated files:');
        $this->info('- app/Models/AuthPackage/');
        $this->info('- app/Http/Controllers/AuthPackage/');
        $this->info('');
        $this->info('You can now customize these files without modifying the package.');
        $this->info('The package will use your extended models and controllers if they exist.');

        return Command::SUCCESS;
    }

    private function createDirectories(): void
    {
        $directories = [
            app_path('Models/AuthPackage'),
            app_path('Http/Controllers/AuthPackage'),
        ];
        foreach ($directories as $directory) {
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
                $this->info("Created directory: {$directory}");
            }
        }
    }

    private function generateExtendableModels(): void
    {
        $models = [
            'User' => 'Kaely\\AuthPackage\\Models\\User',
            'Role' => 'Kaely\\AuthPackage\\Models\\Role',
            'Permission' => 'Kaely\\AuthPackage\\Models\\Permission',
            'Module' => 'Kaely\\AuthPackage\\Models\\Module',
            'RoleCategory' => 'Kaely\\AuthPackage\\Models\\RoleCategory',
            'Person' => 'Kaely\\AuthPackage\\Models\\Person',
            'Branch' => 'Kaely\\AuthPackage\\Models\\Branch',
            'Department' => 'Kaely\\AuthPackage\\Models\\Department',
        ];
        foreach ($models as $modelName => $baseClass) {
            $this->generateExtendableModel($modelName, $baseClass);
        }
    }

    private function generateExtendableModel(string $modelName, string $baseClass): void
    {
        $modelPath = app_path("Models/AuthPackage/{$modelName}.php");
        if (File::exists($modelPath) && !$this->option('force')) {
            $this->warn("Model {$modelName} already exists. Use --force to overwrite.");
            return;
        }
        $stub = $this->getModelStub($modelName, $baseClass);
        File::put($modelPath, $stub);
        $this->info("Generated model: {$modelPath}");
    }

    private function getModelStub(string $modelName, string $baseClass): string
    {
        $namespace = 'App\\Models\\AuthPackage';
        $baseClassName = class_basename($baseClass);
        return <<<PHP
<?php

namespace {$namespace};

use {$baseClass};

/**
 * Extendable {$modelName} model
 *
 * This model extends the package's {$baseClassName} model.
 * You can add custom methods, relationships, or override existing ones here.
 */
class {$modelName} extends {$baseClassName}
{
    // Add your custom methods and relationships here
}
PHP;
    }

    private function generateExtendableControllers(): void
    {
        $controllers = [
            'AuthController' => 'Kaely\\AuthPackage\\Controllers\\AuthController',
            'UserController' => 'Kaely\\AuthPackage\\Controllers\\UserController',
            'RoleController' => 'Kaely\\AuthPackage\\Controllers\\RoleController',
            'PermissionController' => 'Kaely\\AuthPackage\\Controllers\\PermissionController',
            'ModuleController' => 'Kaely\\AuthPackage\\Controllers\\ModuleController',
            'BranchController' => 'Kaely\\AuthPackage\\Controllers\\BranchController',
            'DepartmentController' => 'Kaely\\AuthPackage\\Controllers\\DepartmentController',
            'MenuController' => 'Kaely\\AuthPackage\\Controllers\\MenuController',
        ];
        foreach ($controllers as $controllerName => $baseClass) {
            $this->generateExtendableController($controllerName, $baseClass);
        }
    }

    private function generateExtendableController(string $controllerName, string $baseClass): void
    {
        $controllerPath = app_path("Http/Controllers/AuthPackage/{$controllerName}.php");
        if (File::exists($controllerPath) && !$this->option('force')) {
            $this->warn("Controller {$controllerName} already exists. Use --force to overwrite.");
            return;
        }
        $stub = $this->getControllerStub($controllerName, $baseClass);
        File::put($controllerPath, $stub);
        $this->info("Generated controller: {$controllerPath}");
    }

    private function getControllerStub(string $controllerName, string $baseClass): string
    {
        $namespace = 'App\\Http\\Controllers\\AuthPackage';
        $baseClassName = class_basename($baseClass);
        return <<<PHP
<?php

namespace {$namespace};

use {$baseClass};

/**
 * Extendable {$controllerName}
 *
 * This controller extends the package's {$baseClassName}.
 * You can add custom methods or override existing ones here.
 */
class {$controllerName} extends {$baseClassName}
{
    // Add your custom methods here
}
PHP;
    }
} 