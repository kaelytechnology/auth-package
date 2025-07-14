<?php

namespace Kaely\AuthPackage\Tests;

use Orchestra\Testbench\TestCase;
use Kaely\AuthPackage\AuthPackageServiceProvider;
use Kaely\AuthPackage\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthControllerTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            AuthPackageServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ejecutar migraciones
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        
        // Ejecutar seeders
        $this->artisan('db:seed', [
            '--class' => 'Kaely\AuthPackage\Database\Seeders\AuthPackageSeeder'
        ]);
    }

    /** @test */
    public function it_can_register_a_user()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'is_active',
                        'created_at',
                        'updated_at',
                    ]
                ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    /** @test */
    public function it_can_login_a_user()
    {
        // Crear usuario
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $loginData = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/v1/auth/login', $loginData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'is_active',
                        'created_at',
                        'updated_at',
                    ],
                    'token'
                ]);
    }

    /** @test */
    public function it_cannot_login_with_invalid_credentials()
    {
        $loginData = [
            'email' => 'invalid@example.com',
            'password' => 'wrongpassword',
        ];

        $response = $this->postJson('/api/v1/auth/login', $loginData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_cannot_login_inactive_user()
    {
        // Crear usuario inactivo
        $user = User::create([
            'name' => 'Inactive User',
            'email' => 'inactive@example.com',
            'password' => Hash::make('password123'),
            'is_active' => false,
        ]);

        $loginData = [
            'email' => 'inactive@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/v1/auth/login', $loginData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }
} 