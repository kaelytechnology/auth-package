<?php

namespace Kaely\AuthPackage\Tests;

use Kaely\AuthPackage\Models\User;
use Kaely\AuthPackage\Models\Role;
use Kaely\AuthPackage\Models\Permission;

class UserControllerTest extends TestCase
{
    /** @test */
    public function it_can_list_users()
    {
        $admin = $this->createAdminUser();
        
        // Crear algunos usuarios adicionales
        $this->createUser(['email' => 'user1@example.com']);
        $this->createUser(['email' => 'user2@example.com']);

        $response = $this->authenticatedRequest('get', '/api/v1/auth/users', [], $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJsonStructure([
            'data' => [
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'is_active',
                        'created_at',
                        'updated_at',
                    ]
                ],
                'pagination' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ]
            ]
        ]);
    }

    /** @test */
    public function it_requires_authentication_to_list_users()
    {
        $response = $this->getJson('/api/v1/auth/users');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_create_user()
    {
        $admin = $this->createAdminUser();

        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'is_active' => true,
        ];

        $response = $this->authenticatedRequest('post', '/api/v1/auth/users', $userData, $admin);

        $this->assertSuccessResponse($response, 201);
        $response->assertJson([
            'data' => [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'is_active' => true,
            ]
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
        ]);
    }

    /** @test */
    public function it_validates_required_fields_for_user_creation()
    {
        $admin = $this->createAdminUser();

        $response = $this->authenticatedRequest('post', '/api/v1/auth/users', [], $admin);

        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /** @test */
    public function it_validates_email_uniqueness_for_user_creation()
    {
        $admin = $this->createAdminUser();
        $this->createUser(['email' => 'existing@example.com']);

        $userData = [
            'name' => 'New User',
            'email' => 'existing@example.com',
            'password' => 'password123',
        ];

        $response = $this->authenticatedRequest('post', '/api/v1/auth/users', $userData, $admin);

        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_can_show_user()
    {
        $admin = $this->createAdminUser();
        $user = $this->createUser(['email' => 'show@example.com']);

        $response = $this->authenticatedRequest('get', "/api/v1/auth/users/{$user->id}", [], $admin);

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
    public function it_returns_404_for_nonexistent_user()
    {
        $admin = $this->createAdminUser();

        $response = $this->authenticatedRequest('get', '/api/v1/auth/users/99999', [], $admin);

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_update_user()
    {
        $admin = $this->createAdminUser();
        $user = $this->createUser(['email' => 'update@example.com']);

        $updateData = [
            'name' => 'Updated User',
            'email' => 'updated@example.com',
            'is_active' => false,
        ];

        $response = $this->authenticatedRequest('put', "/api/v1/auth/users/{$user->id}", $updateData, $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJson([
            'data' => [
                'name' => 'Updated User',
                'email' => 'updated@example.com',
                'is_active' => false,
            ]
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated User',
            'email' => 'updated@example.com',
            'is_active' => false,
        ]);
    }

    /** @test */
    public function it_can_update_user_password()
    {
        $admin = $this->createAdminUser();
        $user = $this->createUser(['email' => 'password@example.com']);

        $updateData = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'newpassword123',
        ];

        $response = $this->authenticatedRequest('put', "/api/v1/auth/users/{$user->id}", $updateData, $admin);

        $this->assertSuccessResponse($response, 200);

        // Verificar que la contraseña fue actualizada
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    /** @test */
    public function it_validates_email_uniqueness_for_user_update()
    {
        $admin = $this->createAdminUser();
        $user1 = $this->createUser(['email' => 'user1@example.com']);
        $user2 = $this->createUser(['email' => 'user2@example.com']);

        $updateData = [
            'name' => $user1->name,
            'email' => 'user2@example.com', // Email de otro usuario
        ];

        $response = $this->authenticatedRequest('put', "/api/v1/auth/users/{$user1->id}", $updateData, $admin);

        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_can_delete_user()
    {
        $admin = $this->createAdminUser();
        $user = $this->createUser(['email' => 'delete@example.com']);

        $response = $this->authenticatedRequest('delete', "/api/v1/auth/users/{$user->id}", [], $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJson(['message' => 'User deleted successfully']);

        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    /** @test */
    public function it_can_assign_roles_to_user()
    {
        $admin = $this->createAdminUser();
        $user = $this->createUser(['email' => 'roles@example.com']);
        
        // Crear roles adicionales
        $role1 = Role::create([
            'name' => 'Editor',
            'slug' => 'editor',
            'description' => 'Editor role',
            'role_category_id' => 1,
            'status' => true,
        ]);
        
        $role2 = Role::create([
            'name' => 'Viewer',
            'slug' => 'viewer',
            'description' => 'Viewer role',
            'role_category_id' => 1,
            'status' => true,
        ]);

        $assignData = [
            'roles' => [$role1->id, $role2->id],
        ];

        $response = $this->authenticatedRequest('post', "/api/v1/auth/users/{$user->id}/assign-roles", $assignData, $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJson(['message' => 'Roles assigned successfully']);

        $this->assertDatabaseHas('user_role', [
            'user_id' => $user->id,
            'role_id' => $role1->id,
        ]);
        
        $this->assertDatabaseHas('user_role', [
            'user_id' => $user->id,
            'role_id' => $role2->id,
        ]);
    }

    /** @test */
    public function it_validates_roles_for_assignment()
    {
        $admin = $this->createAdminUser();
        $user = $this->createUser(['email' => 'invalid@example.com']);

        $assignData = [
            'roles' => [99999], // Rol inexistente
        ];

        $response = $this->authenticatedRequest('post', "/api/v1/auth/users/{$user->id}/assign-roles", $assignData, $admin);

        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['roles.0']);
    }

    /** @test */
    public function it_can_get_user_roles()
    {
        $admin = $this->createAdminUser();
        $user = $this->createUser(['email' => 'getroles@example.com']);
        
        // Asignar rol al usuario
        $role = Role::where('slug', 'super-admin')->first();
        $user->roles()->attach($role->id);

        $response = $this->authenticatedRequest('get', "/api/v1/auth/users/{$user->id}/roles", [], $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJsonStructure([
            'user' => [
                'id',
                'name',
                'email',
            ],
            'roles' => [
                '*' => [
                    'id',
                    'name',
                    'slug',
                    'description',
                    'permissions',
                ]
            ]
        ]);
    }

    /** @test */
    public function it_can_get_user_permissions()
    {
        $admin = $this->createAdminUser();
        $user = $this->createUser(['email' => 'permissions@example.com']);
        
        // Asignar rol con permisos al usuario
        $role = Role::where('slug', 'super-admin')->first();
        $user->roles()->attach($role->id);

        $response = $this->authenticatedRequest('get', "/api/v1/auth/users/{$user->id}/permissions", [], $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJsonStructure([
            'user' => [
                'id',
                'name',
                'email',
            ],
            'permissions' => [
                '*' => [
                    'id',
                    'name',
                    'slug',
                    'description',
                ]
            ]
        ]);
    }

    /** @test */
    public function it_can_filter_users_by_status()
    {
        $admin = $this->createAdminUser();
        
        // Crear usuarios con diferentes estados
        $this->createUser(['email' => 'active@example.com', 'is_active' => true]);
        $this->createUser(['email' => 'inactive@example.com', 'is_active' => false]);

        $response = $this->authenticatedRequest('get', '/api/v1/auth/users?status=true', [], $admin);

        $this->assertSuccessResponse($response, 200);
        
        $users = $response->json('data.data');
        foreach ($users as $user) {
            $this->assertTrue($user['is_active']);
        }
    }

    /** @test */
    public function it_can_search_users()
    {
        $admin = $this->createAdminUser();
        
        // Crear usuarios con nombres específicos
        $this->createUser(['name' => 'John Doe', 'email' => 'john@example.com']);
        $this->createUser(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

        $response = $this->authenticatedRequest('get', '/api/v1/auth/users?search=John', [], $admin);

        $this->assertSuccessResponse($response, 200);
        
        $users = $response->json('data.data');
        $this->assertCount(1, $users);
        $this->assertEquals('John Doe', $users[0]['name']);
    }

    /** @test */
    public function it_can_sort_users()
    {
        $admin = $this->createAdminUser();
        
        // Crear usuarios con nombres específicos
        $this->createUser(['name' => 'Zebra', 'email' => 'zebra@example.com']);
        $this->createUser(['name' => 'Alpha', 'email' => 'alpha@example.com']);

        $response = $this->authenticatedRequest('get', '/api/v1/auth/users?sort_by=name&sort_order=asc', [], $admin);

        $this->assertSuccessResponse($response, 200);
        
        $users = $response->json('data.data');
        $this->assertEquals('Alpha', $users[0]['name']);
        $this->assertEquals('Zebra', $users[1]['name']);
    }
} 