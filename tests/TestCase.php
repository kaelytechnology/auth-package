<?php

namespace Kaely\AuthPackage\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Kaely\AuthPackage\AuthPackageServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Kaely\AuthPackage\Models\User;
use Kaely\AuthPackage\Models\Role;
use Kaely\AuthPackage\Models\Permission;
use Kaely\AuthPackage\Models\Module;
use Kaely\AuthPackage\Models\RoleCategory;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [
            AuthPackageServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Configurar base de datos para testing
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Configurar Sanctum
        $app['config']->set('sanctum.stateful', []);
        $app['config']->set('sanctum.guard', ['web']);

        // Configurar variables de entorno para testing
        $app['config']->set('auth-package.auth.password_timeout', env('AUTH_PASSWORD_TIMEOUT', 10800));
        $app['config']->set('auth-package.tokens.expiration', env('AUTH_TOKEN_EXPIRATION', 60 * 24 * 7));
        $app['config']->set('auth-package.tokens.refresh_expiration', env('AUTH_REFRESH_TOKEN_EXPIRATION', 60 * 24 * 30));
        $app['config']->set('auth-package.roles.cache_ttl', env('AUTH_ROLES_CACHE_TTL', 3600));

        // Configurar rutas para testing
        $app['config']->set('auth-package.routes.prefix', 'auth');
        $app['config']->set('auth-package.routes.api_prefix', 'api');
        $app['config']->set('auth-package.routes.version_prefix', 'v1');
        $app['config']->set('auth-package.routes.enable_versioning', true);
        $app['config']->set('auth-package.routes.auto_api_prefix', true);
    }

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ejecutar migraciones
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        
        // Ejecutar seeders básicos
        $this->runBasicSeeders();
    }

    /**
     * Ejecutar seeders básicos para testing
     */
    protected function runBasicSeeders()
    {
        // Crear módulos básicos
        $modules = [
            [
                'name' => 'Authentication',
                'slug' => 'auth',
                'order' => 1,
                'description' => 'Authentication and authorization module',
                'icon' => 'fas fa-shield-alt',
                'route' => '/auth',
                'is_active' => true,
            ],
            [
                'name' => 'Users',
                'slug' => 'users',
                'order' => 2,
                'description' => 'User management module',
                'icon' => 'fas fa-users',
                'route' => '/users',
                'is_active' => true,
            ],
        ];

        foreach ($modules as $moduleData) {
            Module::create($moduleData);
        }

        // Crear categoría de roles
        $roleCategory = RoleCategory::create([
            'name' => 'System',
            'slug' => 'system',
            'description' => 'System level roles',
        ]);

        // Crear rol de admin
        $adminRole = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'description' => 'Super administrator with all permissions',
            'role_category_id' => $roleCategory->id,
            'status' => true,
        ]);

        // Crear permisos básicos
        $permissions = [
            [
                'name' => 'Login',
                'slug' => 'auth.login',
                'description' => 'Can login to the system',
                'module_id' => Module::where('slug', 'auth')->first()->id,
                'status' => true,
            ],
            [
                'name' => 'Logout',
                'slug' => 'auth.logout',
                'description' => 'Can logout from the system',
                'module_id' => Module::where('slug', 'auth')->first()->id,
                'status' => true,
            ],
        ];

        foreach ($permissions as $permissionData) {
            Permission::create($permissionData);
        }

        // Asignar permisos al rol admin
        $adminRole->permissions()->attach(Permission::all()->pluck('id'));
    }

    /**
     * Crear un usuario de prueba
     */
    protected function createUser($attributes = [])
    {
        $defaults = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ];

        return User::create(array_merge($defaults, $attributes));
    }

    /**
     * Crear un usuario administrador
     */
    protected function createAdminUser($attributes = [])
    {
        $user = $this->createUser($attributes);
        
        $adminRole = Role::where('slug', 'super-admin')->first();
        if ($adminRole) {
            $user->roles()->attach($adminRole->id);
        }

        return $user;
    }

    /**
     * Obtener token de autenticación para un usuario
     */
    protected function getAuthToken($user = null)
    {
        if (!$user) {
            $user = $this->createUser();
        }

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        return $response->json('token');
    }

    /**
     * Hacer request autenticado
     */
    protected function authenticatedRequest($method, $uri, $data = [], $user = null)
    {
        $token = $this->getAuthToken($user);
        
        return $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->$method($uri, $data);
    }

    /**
     * Assert que la respuesta tiene estructura de error
     */
    protected function assertErrorResponse($response, $status = 422)
    {
        $response->assertStatus($status)
                ->assertJsonStructure([
                    'message',
                    'errors' => [
                        '*' => [
                            '*'
                        ]
                    ]
                ]);
    }

    /**
     * Assert que la respuesta tiene estructura de éxito
     */
    protected function assertSuccessResponse($response, $status = 200)
    {
        $response->assertStatus($status)
                ->assertJsonStructure([
                    'message',
                    'data'
                ]);
    }
} 