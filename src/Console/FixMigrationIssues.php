<?php

namespace Kaely\AuthPackage\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class FixMigrationIssues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth-package:fix-migrations {--force : Force the fix}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix common migration issues with the authentication package';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Fixing migration issues...');

        // Verificar si la tabla modules existe
        if (!Schema::hasTable('modules')) {
            $this->error('Modules table does not exist. Please run migrations first.');
            $this->info('Run: php artisan migrate');
            return Command::FAILURE;
        }

        // Verificar si la columna 'order' existe en modules
        if (!Schema::hasColumn('modules', 'order')) {
            $this->warn('Column "order" not found in modules table. Adding it...');
            
            // Crear migración temporal para agregar la columna
            $this->createOrderColumnMigration();
            
            $this->info('Column "order" added successfully.');
        }

        // Verificar si la tabla users tiene las columnas necesarias
        if (Schema::hasTable('users')) {
            $requiredColumns = ['is_active', 'deleted_at', 'user_add', 'user_edit', 'user_deleted'];
            
            foreach ($requiredColumns as $column) {
                if (!Schema::hasColumn('users', $column)) {
                    $this->warn("Column '{$column}' not found in users table. Adding it...");
                    $this->addColumnToUsers($column);
                }
            }
        }

        $this->info('Migration issues fixed successfully!');
        $this->info('');
        $this->info('You can now run the seeder:');
        $this->info('php artisan db:seed --class=AuthPackageSeeder');

        return Command::SUCCESS;
    }

    /**
     * Create a migration to add the order column to modules table
     */
    private function createOrderColumnMigration(): void
    {
        $migrationName = 'add_order_to_modules_table';
        $timestamp = date('Y_m_d_His');
        $filename = "{$timestamp}_{$migrationName}.php";
        $path = database_path("migrations/{$filename}");

        $content = "<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules', function (Blueprint \$table) {
            \$table->integer('order')->default(0)->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint \$table) {
            \$table->dropColumn('order');
        });
    }
};";

        file_put_contents($path, $content);
        
        // Ejecutar la migración
        $this->callSilent('migrate');
    }

    /**
     * Add a column to the users table
     */
    private function addColumnToUsers(string $column): void
    {
        $migrationName = "add_{$column}_to_users_table";
        $timestamp = date('Y_m_d_His');
        $filename = "{$timestamp}_{$migrationName}.php";
        $path = database_path("migrations/{$filename}");

        $columnDefinition = match($column) {
            'is_active' => '$table->boolean("is_active")->default(true);',
            'deleted_at' => '$table->softDeletes();',
            'user_add' => '$table->unsignedBigInteger("user_add")->nullable();',
            'user_edit' => '$table->unsignedBigInteger("user_edit")->nullable();',
            'user_deleted' => '$table->unsignedBigInteger("user_deleted")->nullable();',
            default => '$table->string("' . $column . '");'
        };

        $foreignKeyDefinition = match($column) {
            'user_add' => '$table->foreign("user_add")->references("id")->on("users")->onDelete("set null");',
            'user_edit' => '$table->foreign("user_edit")->references("id")->on("users")->onDelete("set null");',
            'user_deleted' => '$table->foreign("user_deleted")->references("id")->on("users")->onDelete("set null");',
            default => ''
        };

        $content = "<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint \$table) {
            {$columnDefinition}
            {$foreignKeyDefinition}
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint \$table) {
            \$table->dropColumn('{$column}');
        });
    }
};";

        file_put_contents($path, $content);
        
        // Ejecutar la migración
        $this->callSilent('migrate');
    }
} 