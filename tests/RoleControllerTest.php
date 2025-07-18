<?php

namespace Kaely\AuthPackage\Tests;

use Kaely\AuthPackage\Models\Role;
use Kaely\AuthPackage\Models\Permission;
use Kaely\AuthPackage\Models\RoleCategory;

class RoleControllerTest extends TestCase
{
    /** @test */
    public function it_can_list_roles()
    {
        $admin = $this->createAdminUser();
        
        // Crear roles adicionales
        $this->createRole(['name' => 'Editor', 'slug' => 'editor']);
        $this->createRole(['name' => 'Viewer', 'slug' => 'viewer']);

        $response = $this->authenticatedRequest('get', '/api/v1/auth/roles', [], $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJsonStructure([
            'data' => [
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'description',
                        'status',
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
    public function it_requires_authentication_to_list_roles()
    {
        $response = $this->getJson('/api/v1/auth/roles');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_create_role()
    {
        $admin = $this->createAdminUser();
        $category = RoleCategory::first();

        $roleData = [
            'name' => 'New Role',
            'slug' => 'new-role',
            'description' => 'A new role for testing',
            'role_category_id' => $category->id,
            'status' => true,
        ];

        $response = $this->authenticatedRequest('post', '/api/v1/auth/roles', $roleData, $admin);

        $this->assertSuccessResponse($response, 201);
        $response->assertJson([
            'data' => [
                'name' => 'New Role',
                'slug' => 'new-role',
                'description' => 'A new role for testing',
                'status' => true,
            ]
        ]);

        $this->assertDatabaseHas('roles', [
            'name' => 'New Role',
            'slug' => 'new-role',
        ]);
    }

    /** @test */
    public function it_validates_required_fields_for_role_creation()
    {
        $admin = $this->createAdminUser();

        $response = $this->authenticatedRequest('post', '/api/v1/auth/roles', [], $admin);

        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['name', 'slug', 'role_category_id']);
    }

    /** @test */
    public function it_validates_slug_uniqueness_for_role_creation()
    {
        $admin = $this->createAdminUser();
        $category = RoleCategory::first();
        
        $this->createRole(['slug' => 'existing-role']);

        $roleData = [
            'name' => 'Another Role',
            'slug' => 'existing-role',
            'description' => 'Another role',
            'role_category_id' => $category->id,
        ];

        $response = $this->authenticatedRequest('post', '/api/v1/auth/roles', $roleData, $admin);

        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['slug']);
    }

    /** @test */
    public function it_can_show_role()
    {
        $admin = $this->createAdminUser();
        $role = $this->createRole(['name' => 'Show Role', 'slug' => 'show-role']);

        $response = $this->authenticatedRequest('get', "/api/v1/auth/roles/{$role->id}", [], $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJson([
            'data' => [
                'id' => $role->id,
                'name' => 'Show Role',
                'slug' => 'show-role',
            ]
        ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_role()
    {
        $admin = $this->createAdminUser();

        $response = $this->authenticatedRequest('get', '/api/v1/auth/roles/99999', [], $admin);

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_update_role()
    {
        $admin = $this->createAdminUser();
        $role = $this->createRole(['name' => 'Update Role', 'slug' => 'update-role']);

        $updateData = [
            'name' => 'Updated Role',
            'slug' => 'updated-role',
            'description' => 'Updated description',
            'status' => false,
        ];

        $response = $this->authenticatedRequest('put', "/api/v1/auth/roles/{$role->id}", $updateData, $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJson([
            'data' => [
                'name' => 'Updated Role',
                'slug' => 'updated-role',
                'description' => 'Updated description',
                'status' => false,
            ]
        ]);

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => 'Updated Role',
            'slug' => 'updated-role',
            'status' => false,
        ]);
    }

    /** @test */
    public function it_validates_slug_uniqueness_for_role_update()
    {
        $admin = $this->createAdminUser();
        $role1 = $this->createRole(['slug' => 'role1']);
        $role2 = $this->createRole(['slug' => 'role2']);

        $updateData = [
            'name' => $role1->name,
            'slug' => 'role2', // Slug de otro rol
        ];

        $response = $this->authenticatedRequest('put', "/api/v1/auth/roles/{$role1->id}", $updateData, $admin);

        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['slug']);
    }

    /** @test */
    public function it_can_delete_role()
    {
        $admin = $this->createAdminUser();
        $role = $this->createRole(['name' => 'Delete Role', 'slug' => 'delete-role']);

        $response = $this->authenticatedRequest('delete', "/api/v1/auth/roles/{$role->id}", [], $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJson(['message' => 'Role deleted successfully']);

        $this->assertSoftDeleted('roles', ['id' => $role->id]);
    }

    /** @test */
    public function it_can_assign_permissions_to_role()
    {
        $admin = $this->createAdminUser();
        $role = $this->createRole(['name' => 'Permission Role', 'slug' => 'permission-role']);
        
        // Crear permisos
        $permission1 = $this->createPermission(['name' => 'Read', 'slug' => 'read']);
        $permission2 = $this->createPermission(['name' => 'Write', 'slug' => 'write']);

        $assignData = [
            'permissions' => [$permission1->id, $permission2->id],
        ];

        $response = $this->authenticatedRequest('post', "/api/v1/auth/roles/{$role->id}/assign-permissions", $assignData, $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJson(['message' => 'Permissions assigned successfully']);

        $this->assertDatabaseHas('role_permission', [
            'role_id' => $role->id,
            'permission_id' => $permission1->id,
        ]);
        
        $this->assertDatabaseHas('role_permission', [
            'role_id' => $role->id,
            'permission_id' => $permission2->id,
        ]);
    }

    /** @test */
    public function it_validates_permissions_for_assignment()
    {
        $admin = $this->createAdminUser();
        $role = $this->createRole(['name' => 'Invalid Role', 'slug' => 'invalid-role']);

        $assignData = [
            'permissions' => [99999], // Permiso inexistente
        ];

        $response = $this->authenticatedRequest('post', "/api/v1/auth/roles/{$role->id}/assign-permissions", $assignData, $admin);

        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['permissions.0']);
    }

    /** @test */
    public function it_can_get_role_permissions()
    {
        $admin = $this->createAdminUser();
        $role = $this->createRole(['name' => 'Get Permissions Role', 'slug' => 'get-permissions-role']);
        
        // Asignar permisos al rol
        $permission = $this->createPermission(['name' => 'Test Permission', 'slug' => 'test-permission']);
        $role->permissions()->attach($permission->id);

        $response = $this->authenticatedRequest('get', "/api/v1/auth/roles/{$role->id}/permissions", [], $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJsonStructure([
            'role' => [
                'id',
                'name',
                'slug',
                'description',
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
    public function it_can_get_active_roles()
    {
        $admin = $this->createAdminUser();
        
        // Crear roles con diferentes estados
        $this->createRole(['name' => 'Active Role', 'slug' => 'active-role', 'status' => true]);
        $this->createRole(['name' => 'Inactive Role', 'slug' => 'inactive-role', 'status' => false]);

        $response = $this->authenticatedRequest('get', '/api/v1/auth/roles/active', [], $admin);

        $this->assertSuccessResponse($response, 200);
        
        $roles = $response->json('data');
        foreach ($roles as $role) {
            $this->assertTrue($role['status']);
        }
    }

    /** @test */
    public function it_can_search_roles()
    {
        $admin = $this->createAdminUser();
        
        // Crear roles con nombres específicos
        $this->createRole(['name' => 'Admin Role', 'slug' => 'admin-role']);
        $this->createRole(['name' => 'User Role', 'slug' => 'user-role']);

        $response = $this->authenticatedRequest('get', '/api/v1/auth/roles?search=Admin', [], $admin);

        $this->assertSuccessResponse($response, 200);
        
        $roles = $response->json('data.data');
        $this->assertCount(1, $roles);
        $this->assertEquals('Admin Role', $roles[0]['name']);
    }

    /** @test */
    public function it_can_sort_roles()
    {
        $admin = $this->createAdminUser();
        
        // Crear roles con nombres específicos
        $this->createRole(['name' => 'Zebra Role', 'slug' => 'zebra-role']);
        $this->createRole(['name' => 'Alpha Role', 'slug' => 'alpha-role']);

        $response = $this->authenticatedRequest('get', '/api/v1/auth/roles?sort_by=name&sort_order=asc', [], $admin);

        $this->assertSuccessResponse($response, 200);
        
        $roles = $response->json('data.data');
        $this->assertEquals('Alpha Role', $roles[0]['name']);
        $this->assertEquals('Zebra Role', $roles[1]['name']);
    }

    /**
     * Helper para crear roles
     */
    private function createRole($attributes = [])
    {
        $defaults = [
            'name' => 'Test Role',
            'slug' => 'test-role',
            'description' => 'Test role description',
            'role_category_id' => RoleCategory::first()->id,
            'status' => true,
        ];

        return Role::create(array_merge($defaults, $attributes));
    }

    /**
     * Helper para crear permisos
     */
    private function createPermission($attributes = [])
    {
        $defaults = [
            'name' => 'Test Permission',
            'slug' => 'test.permission',
            'description' => 'Test permission description',
            'module_id' => 1,
            'status' => true,
        ];

        return Permission::create(array_merge($defaults, $attributes));
    }
} 