<?php

namespace Kaely\AuthPackage\Tests;

use Kaely\AuthPackage\Models\User;
use Kaely\AuthPackage\Models\Role;
use Kaely\AuthPackage\Models\RoleCategory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserRoleControllerTest extends TestCase
{
    /** @test */
    public function it_can_list_user_role_assignments()
    {
        $admin = $this->createAdminUser();
        
        // Crear usuarios y roles adicionales
        $user1 = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'is_active' => true
        ]);
        
        $user2 = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password'),
            'is_active' => true
        ]);
        
        $category = RoleCategory::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test category',
            'is_active' => true
        ]);
        
        $role1 = Role::create([
            'name' => 'Editor',
            'slug' => 'editor',
            'description' => 'Editor role',
            'role_category_id' => $category->id,
            'status' => true,
        ]);
        
        $role2 = Role::create([
            'name' => 'Viewer',
            'slug' => 'viewer',
            'description' => 'Viewer role',
            'role_category_id' => $category->id,
            'status' => true,
        ]);
        
        // Asignar roles
        $user1->roles()->attach($role1->id);
        $user2->roles()->attach([$role1->id, $role2->id]);

        $response = $this->authenticatedRequest('get', '/api/v1/auth/user-roles', [], $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'user_id',
                    'role_id',
                    'user_name',
                    'user_email',
                    'role_name',
                    'role_slug',
                    'category_name',
                    'created_at'
                ]
            ],
            'pagination' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
            ]
        ]);
    }

    /** @test */
    public function it_requires_authentication_to_list_user_roles()
    {
        $response = $this->getJson('/api/v1/auth/user-roles');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_assign_role_to_user()
    {
        $admin = $this->createAdminUser();
        
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'is_active' => true
        ]);
        
        $category = RoleCategory::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test category',
            'is_active' => true
        ]);
        
        $role = Role::create([
            'name' => 'Test Role',
            'slug' => 'test-role',
            'description' => 'Test role',
            'role_category_id' => $category->id,
            'status' => true,
        ]);

        $assignmentData = [
            'user_id' => $user->id,
            'role_id' => $role->id,
        ];

        $response = $this->authenticatedRequest('post', '/api/v1/auth/user-roles', $assignmentData, $admin);

        $this->assertSuccessResponse($response, 201);
        $response->assertJson([
            'message' => 'Role assigned to user successfully',
            'data' => [
                'user_id' => $user->id,
                'user_name' => 'Test User',
                'role_id' => $role->id,
                'role_name' => 'Test Role',
            ]
        ]);

        $this->assertDatabaseHas('user_role', [
            'user_id' => $user->id,
            'role_id' => $role->id,
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_assigning_role()
    {
        $admin = $this->createAdminUser();

        $response = $this->authenticatedRequest('post', '/api/v1/auth/user-roles', [], $admin);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['user_id', 'role_id']);
    }

    /** @test */
    public function it_prevents_duplicate_role_assignment()
    {
        $admin = $this->createAdminUser();
        
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'is_active' => true
        ]);
        
        $category = RoleCategory::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test category',
            'is_active' => true
        ]);
        
        $role = Role::create([
            'name' => 'Test Role',
            'slug' => 'test-role',
            'description' => 'Test role',
            'role_category_id' => $category->id,
            'status' => true,
        ]);
        
        // Asignar rol por primera vez
        $user->roles()->attach($role->id);

        $assignmentData = [
            'user_id' => $user->id,
            'role_id' => $role->id,
        ];

        $response = $this->authenticatedRequest('post', '/api/v1/auth/user-roles', $assignmentData, $admin);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'User already has this role assigned'
        ]);
    }

    /** @test */
    public function it_can_remove_role_from_user()
    {
        $admin = $this->createAdminUser();
        
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'is_active' => true
        ]);
        
        $category = RoleCategory::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test category',
            'is_active' => true
        ]);
        
        $role = Role::create([
            'name' => 'Test Role',
            'slug' => 'test-role',
            'description' => 'Test role',
            'role_category_id' => $category->id,
            'status' => true,
        ]);
        
        // Asignar rol primero
        $user->roles()->attach($role->id);

        $removalData = [
            'user_id' => $user->id,
            'role_id' => $role->id,
        ];

        $response = $this->authenticatedRequest('delete', '/api/v1/auth/user-roles', $removalData, $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJson([
            'message' => 'Role removed from user successfully'
        ]);

        $this->assertDatabaseMissing('user_role', [
            'user_id' => $user->id,
            'role_id' => $role->id,
        ]);
    }

    /** @test */
    public function it_handles_removing_non_assigned_role()
    {
        $admin = $this->createAdminUser();
        
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'is_active' => true
        ]);
        
        $category = RoleCategory::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test category',
            'is_active' => true
        ]);
        
        $role = Role::create([
            'name' => 'Test Role',
            'slug' => 'test-role',
            'description' => 'Test role',
            'role_category_id' => $category->id,
            'status' => true,
        ]);

        $removalData = [
            'user_id' => $user->id,
            'role_id' => $role->id,
        ];

        $response = $this->authenticatedRequest('delete', '/api/v1/auth/user-roles', $removalData, $admin);

        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'User does not have this role assigned'
        ]);
    }

    /** @test */
    public function it_can_bulk_assign_roles()
    {
        $admin = $this->createAdminUser();
        
        $users = [];
        for ($i = 1; $i <= 3; $i++) {
            $users[] = User::create([
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'password' => Hash::make('password'),
                'is_active' => true
            ]);
        }
        
        $category = RoleCategory::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test category',
            'is_active' => true
        ]);
        
        $roles = [];
        for ($i = 1; $i <= 2; $i++) {
            $roles[] = Role::create([
                'name' => "Role {$i}",
                'slug' => "role-{$i}",
                'description' => "Role {$i}",
                'role_category_id' => $category->id,
                'status' => true,
            ]);
        }

        $bulkData = [
            'user_ids' => collect($users)->pluck('id')->toArray(),
            'role_ids' => collect($roles)->pluck('id')->toArray(),
        ];

        $response = $this->authenticatedRequest('post', '/api/v1/auth/user-roles/bulk-assign', $bulkData, $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJson([
            'message' => 'Bulk role assignment completed',
            'data' => [
                'assigned_count' => 6, // 3 users × 2 roles
                'skipped_count' => 0,
                'total_users' => 3,
                'total_roles' => 2
            ]
        ]);

        // Verificar que todas las asignaciones se crearon
        foreach ($users as $user) {
            foreach ($roles as $role) {
                $this->assertDatabaseHas('user_role', [
                    'user_id' => $user->id,
                    'role_id' => $role->id,
                ]);
            }
        }
    }

    /** @test */
    public function it_can_bulk_remove_roles()
    {
        $admin = $this->createAdminUser();
        
        $users = [];
        for ($i = 1; $i <= 2; $i++) {
            $users[] = User::create([
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'password' => Hash::make('password'),
                'is_active' => true
            ]);
        }
        
        $category = RoleCategory::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test category',
            'is_active' => true
        ]);
        
        $roles = [];
        for ($i = 1; $i <= 2; $i++) {
            $roles[] = Role::create([
                'name' => "Role {$i}",
                'slug' => "role-{$i}",
                'description' => "Role {$i}",
                'role_category_id' => $category->id,
                'status' => true,
            ]);
        }
        
        // Asignar roles primero
        foreach ($users as $user) {
            foreach ($roles as $role) {
                $user->roles()->attach($role->id);
            }
        }

        $bulkData = [
            'user_ids' => collect($users)->pluck('id')->toArray(),
            'role_ids' => collect($roles)->pluck('id')->toArray(),
        ];

        $response = $this->authenticatedRequest('post', '/api/v1/auth/user-roles/bulk-remove', $bulkData, $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJson([
            'message' => 'Bulk role removal completed',
            'data' => [
                'removed_count' => 4, // 2 users × 2 roles
                'total_users' => 2,
                'total_roles' => 2
            ]
        ]);

        // Verificar que todas las asignaciones se removieron
        foreach ($users as $user) {
            foreach ($roles as $role) {
                $this->assertDatabaseMissing('user_role', [
                    'user_id' => $user->id,
                    'role_id' => $role->id,
                ]);
            }
        }
    }

    /** @test */
    public function it_can_get_users_by_role()
    {
        $admin = $this->createAdminUser();
        
        $users = [];
        for ($i = 1; $i <= 3; $i++) {
            $users[] = User::create([
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'password' => Hash::make('password'),
                'is_active' => true
            ]);
        }
        
        $category = RoleCategory::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test category',
            'is_active' => true
        ]);
        
        $role = Role::create([
            'name' => 'Test Role',
            'slug' => 'test-role',
            'description' => 'Test role',
            'role_category_id' => $category->id,
            'status' => true,
        ]);
        
        // Asignar rol a algunos usuarios
        $users[0]->roles()->attach($role->id);
        $users[1]->roles()->attach($role->id);

        $response = $this->authenticatedRequest('get', "/api/v1/auth/user-roles/by-role/{$role->id}", [], $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJsonStructure([
            'role' => [
                'id',
                'name',
                'slug',
            ],
            'users' => [
                '*' => [
                    'id',
                    'name',
                    'email',
                ]
            ],
            'total_users'
        ]);
        
        $response->assertJson([
            'total_users' => 2
        ]);
    }

    /** @test */
    public function it_can_get_roles_by_user()
    {
        $admin = $this->createAdminUser();
        
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'is_active' => true
        ]);
        
        $category = RoleCategory::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test category',
            'is_active' => true
        ]);
        
        $roles = [];
        for ($i = 1; $i <= 2; $i++) {
            $roles[] = Role::create([
                'name' => "Role {$i}",
                'slug' => "role-{$i}",
                'description' => "Role {$i}",
                'role_category_id' => $category->id,
                'status' => true,
            ]);
        }
        
        // Asignar roles al usuario
        foreach ($roles as $role) {
            $user->roles()->attach($role->id);
        }

        $response = $this->authenticatedRequest('get', "/api/v1/auth/user-roles/by-user/{$user->id}", [], $admin);

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
                ]
            ],
            'total_roles'
        ]);
        
        $response->assertJson([
            'total_roles' => 2
        ]);
    }

    /** @test */
    public function it_can_sync_user_roles()
    {
        $admin = $this->createAdminUser();
        
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'is_active' => true
        ]);
        
        $category = RoleCategory::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test category',
            'is_active' => true
        ]);
        
        $roles = [];
        for ($i = 1; $i <= 3; $i++) {
            $roles[] = Role::create([
                'name' => "Role {$i}",
                'slug' => "role-{$i}",
                'description' => "Role {$i}",
                'role_category_id' => $category->id,
                'status' => true,
            ]);
        }
        
        // Asignar roles iniciales
        $user->roles()->attach([$roles[0]->id, $roles[1]->id]);

        $syncData = [
            'role_ids' => [$roles[1]->id, $roles[2]->id], // Mantener role 1, quitar role 0, agregar role 2
        ];

        $response = $this->authenticatedRequest('post', "/api/v1/auth/user-roles/sync/{$user->id}", $syncData, $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJson([
            'message' => 'User roles synchronized successfully'
        ]);

        // Verificar que la sincronización funcionó
        $this->assertDatabaseMissing('user_role', [
            'user_id' => $user->id,
            'role_id' => $roles[0]->id,
        ]);
        
        $this->assertDatabaseHas('user_role', [
            'user_id' => $user->id,
            'role_id' => $roles[1]->id,
        ]);
        
        $this->assertDatabaseHas('user_role', [
            'user_id' => $user->id,
            'role_id' => $roles[2]->id,
        ]);
    }

    /** @test */
    public function it_can_get_user_role_statistics()
    {
        $admin = $this->createAdminUser();
        
        // Crear datos de prueba
        $users = [];
        for ($i = 1; $i <= 3; $i++) {
            $users[] = User::create([
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'password' => Hash::make('password'),
                'is_active' => true
            ]);
        }
        
        $category = RoleCategory::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test category',
            'is_active' => true
        ]);
        
        $roles = [];
        for ($i = 1; $i <= 2; $i++) {
            $roles[] = Role::create([
                'name' => "Role {$i}",
                'slug' => "role-{$i}",
                'description' => "Role {$i}",
                'role_category_id' => $category->id,
                'status' => true,
            ]);
        }
        
        // Asignar algunos roles
        $users[0]->roles()->attach([$roles[0]->id, $roles[1]->id]);
        $users[1]->roles()->attach($roles[0]->id);
        // users[2] sin roles

        $response = $this->authenticatedRequest('get', '/api/v1/auth/user-roles/statistics', [], $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJsonStructure([
            'total_assignments',
            'users_with_roles',
            'avg_roles_per_user',
            'roles_with_users',
            'avg_users_per_role',
            'users_without_roles',
            'roles_without_users',
            'top_roles'
        ]);
        
        $response->assertJson([
            'total_assignments' => 3,
            'users_with_roles' => 2,
            'users_without_roles' => 1,
            'roles_without_users' => 0
        ]);
    }
}