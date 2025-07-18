<?php

namespace Kaely\AuthPackage\Tests;

use Kaely\AuthPackage\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthControllerTest extends TestCase
{
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
                    'data' => [
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
    public function it_validates_required_fields_for_registration()
    {
        $response = $this->postJson('/api/v1/auth/register', []);

        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /** @test */
    public function it_validates_email_uniqueness_for_registration()
    {
        // Crear usuario existente
        $this->createUser(['email' => 'existing@example.com']);

        $userData = [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_validates_password_confirmation_for_registration()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword',
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function it_can_login_a_user()
    {
        $user = $this->createUser();

        $loginData = [
            'email' => $user->email,
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/v1/auth/login', $loginData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'user' => [
                            'id',
                            'name',
                            'email',
                            'is_active',
                            'created_at',
                            'updated_at',
                        ],
                        'token'
                    ]
                ]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    /** @test */
    public function it_cannot_login_with_invalid_credentials()
    {
        $loginData = [
            'email' => 'invalid@example.com',
            'password' => 'wrongpassword',
        ];

        $response = $this->postJson('/api/v1/auth/login', $loginData);

        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_cannot_login_inactive_user()
    {
        $user = $this->createUser([
            'email' => 'inactive@example.com',
            'is_active' => false,
        ]);

        $loginData = [
            'email' => 'inactive@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/v1/auth/login', $loginData);

        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_validates_required_fields_for_login()
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['email', 'password']);
    }

    /** @test */
    public function it_can_logout_user()
    {
        $user = $this->createUser();
        $token = $this->getAuthToken($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/auth/logout');

        $this->assertSuccessResponse($response, 200);
        $response->assertJson(['message' => 'Logged out successfully']);
    }

    /** @test */
    public function it_requires_authentication_for_logout()
    {
        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_get_current_user_info()
    {
        $user = $this->createUser();

        $response = $this->authenticatedRequest('get', '/api/v1/auth/me', [], $user);

        $this->assertSuccessResponse($response, 200);
        $response->assertJson([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]
        ]);
    }

    /** @test */
    public function it_requires_authentication_for_me_endpoint()
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_refresh_token()
    {
        $user = $this->createUser();
        $token = $this->getAuthToken($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/auth/refresh');

        $this->assertSuccessResponse($response, 200);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'token'
            ]
        ]);

        $this->assertNotEmpty($response->json('data.token'));
        $this->assertNotEquals($token, $response->json('data.token'));
    }

    /** @test */
    public function it_requires_authentication_for_refresh()
    {
        $response = $this->postJson('/api/v1/auth/refresh');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_validates_password_length_for_registration()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '123',
            'password_confirmation' => '123',
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function it_validates_email_format_for_registration()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_validates_email_format_for_login()
    {
        $loginData = [
            'email' => 'invalid-email',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/v1/auth/login', $loginData);

        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['email']);
    }
} 