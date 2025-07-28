<?php

namespace Kaely\AuthPackage\Tests;

use Kaely\AuthPackage\Models\Permission;
use Kaely\AuthPackage\Models\Module;
use Kaely\AuthPackage\Models\User;
use Kaely\AuthPackage\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class PermissionControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_permissions()
    {
        $admin = $this->createAdminUser();
        
        $module = Module::create([
            'name' => 'Test Module',
            'slug' => 'test-module',
            'order' => 1,
            'status' => true,
        ]);
        
        Permission::create([
            'name' => 'Test Permission 1',
            'slug' => 'test.permission1',
            'description' => 'Test permission description',
            'module_id' => $module->id,
            'status' => true,
        ]);
        
        Permission::create([
            'name' => 'Test Permission 2',
            'slug' => 'test.permission2',
            'description' => 'Another test permission',
            'module_id' => $module->id,
            'status' => false,
        ]);

        $response = $this->authenticatedRequest('get', '/api/v1/auth/permissions', [], $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJsonStructure([
            'data',
            'links',
            'meta'
        ]);
    }

    /** @test */
    public function it_can_search_permissions()
    {
        $admin = $this->createAdminUser();
        
        $module = Module::create([
            'name' => 'Test Module',
            'slug' => 'test-module',
            'order' => 1,
            'status' => true,
        ]);
        
        Permission::create([
            'name' => 'User Create',
            'slug' => 'users.create',
            'description' => 'Create users',
            'module_id' => $module->id,
            'status' => true,
        ]);
        
        Permission::create([
            'name' => 'Role Create',
            'slug' => 'roles.create',
            'description' => 'Create roles',
            'module_id' => $module->id,
            'status' => true,
        ]);

        $response = $this->authenticatedRequest('get', '/api/v1/auth/permissions?search=user', [], $admin);

        $this->assertSuccessResponse($response, 200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('User Create', $data[0]['name']);
    }

    /** @test */
    public function it_can_filter_permissions_by_module()
    {
        $admin = $this->createAdminUser();
        
        $module1 = Module::create([
            'name' => 'Module 1',
            'slug' => 'module-1',
            'order' => 1,
            'status' => true,
        ]);
        
        $module2 = Module::create([
            'name' => 'Module 2',
            'slug' => 'module-2',
            'order' => 2,
            'status' => true,
        ]);
        
        Permission::create([
            'name' => 'Permission Module 1',
            'slug' => 'module1.permission',
            'module_id' => $module1->id,
            'status' => true,
        ]);
        
        Permission::create([
            'name' => 'Permission Module 2',
            'slug' => 'module2.permission',
            'module_id' => $module2->id,
            'status' => true,
        ]);

        $response = $this->authenticatedRequest('get', "/api/v1/auth/permissions?module_id={$module1->id}", [], $admin);

        $this->assertSuccessResponse($response, 200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Permission Module 1', $data[0]['name']);
    }

    /** @test */
    public function it_can_filter_permissions_by_status()
    {
        $admin = $this->createAdminUser();
        
        $module = Module::create([
            'name' => 'Test Module',
            'slug' => 'test-module',
            'order' => 1,
            'status' => true,
        ]);
        
        Permission::create([
            'name' => 'Active Permission',
            'slug' => 'active.permission',
            'module_id' => $module->id,
            'status' => true,
        ]);
        
        Permission::create([
            'name' => 'Inactive Permission',
            'slug' => 'inactive.permission',
            'module_id' => $module->id,
            'status' => false,
        ]);

        $response = $this->authenticatedRequest('get', '/api/v1/auth/permissions?status=1', [], $admin);

        $this->assertSuccessResponse($response, 200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Active Permission', $data[0]['name']);
    }

    /** @test */
    public function it_can_show_specific_permission()
    {
        $admin = $this->createAdminUser();
        
        $module = Module::create([
            'name' => 'Test Module',
            'slug' => 'test-module',
            'order' => 1,
            'status' => true,
        ]);
        
        $permission = Permission::create([
            'name' => 'Test Permission',
            'slug' => 'test.permission',
            'description' => 'Test permission description',
            'module_id' => $module->id,
            'status' => true,
        ]);

        $response = $this->authenticatedRequest('get', "/api/v1/auth/permissions/{$permission->id}", [], $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'slug',
                'description',
                'module_id',
                'status',
                'module'
            ]
        ]);
        $this->assertEquals('Test Permission', $response->json('data.name'));
    }

    /** @test */
    public function it_can_update_permission()
    {
        $admin = $this->createAdminUser();
        
        $module = Module::create([
            'name' => 'Test Module',
            'slug' => 'test-module',
            'order' => 1,
            'status' => true,
        ]);
        
        $permission = Permission::create([
            'name' => 'Original Permission',
            'slug' => 'original.permission',
            'description' => 'Original description',
            'module_id' => $module->id,
            'status' => true,
        ]);

        $updateData = [
            'name' => 'Updated Permission',
            'slug' => 'updated.permission',
            'description' => 'Updated description',
            'module_id' => $module->id,
            'status' => false,
        ];

        $response = $this->authenticatedRequest('put', "/api/v1/auth/permissions/{$permission->id}", $updateData, $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJson([
            'message' => 'Permission updated successfully'
        ]);
        
        $permission->refresh();
        $this->assertEquals('Updated Permission', $permission->name);
        $this->assertEquals('updated.permission', $permission->slug);
        $this->assertEquals('Updated description', $permission->description);
        $this->assertFalse($permission->status);
    }

    /** @test */
    public function it_validates_required_fields_when_updating_permission()
    {
        $admin = $this->createAdminUser();
        
        $module = Module::create([
            'name' => 'Test Module',
            'slug' => 'test-module',
            'order' => 1,
            'status' => true,
        ]);
        
        $permission = Permission::create([
            'name' => 'Test Permission',
            'slug' => 'test.permission',
            'module_id' => $module->id,
            'status' => true,
        ]);

        $updateData = [
            'name' => '',
            'slug' => '',
            'module_id' => '',
        ];

        $response = $this->authenticatedRequest('put', "/api/v1/auth/permissions/{$permission->id}", $updateData, $admin);

        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['name', 'slug', 'module_id']);
    }

    /** @test */
    public function it_validates_unique_slug_when_updating_permission()
    {
        $admin = $this->createAdminUser();
        
        $module = Module::create([
            'name' => 'Test Module',
            'slug' => 'test-module',
            'order' => 1,
            'status' => true,
        ]);
        
        $permission1 = Permission::create([
            'name' => 'Permission 1',
            'slug' => 'permission.1',
            'module_id' => $module->id,
            'status' => true,
        ]);
        
        $permission2 = Permission::create([
            'name' => 'Permission 2',
            'slug' => 'permission.2',
            'module_id' => $module->id,
            'status' => true,
        ]);

        $updateData = [
            'name' => 'Updated Permission 2',
            'slug' => 'permission.1', // Slug ya existe
            'module_id' => $module->id,
        ];

        $response = $this->authenticatedRequest('put', "/api/v1/auth/permissions/{$permission2->id}", $updateData, $admin);

        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['slug']);
    }

    /** @test */
    public function it_validates_module_exists_when_updating_permission()
    {
        $admin = $this->createAdminUser();
        
        $module = Module::create([
            'name' => 'Test Module',
            'slug' => 'test-module',
            'order' => 1,
            'status' => true,
        ]);
        
        $permission = Permission::create([
            'name' => 'Test Permission',
            'slug' => 'test.permission',
            'module_id' => $module->id,
            'status' => true,
        ]);

        $updateData = [
            'name' => 'Updated Permission',
            'slug' => 'updated.permission',
            'module_id' => 999, // ID inexistente
        ];

        $response = $this->authenticatedRequest('put', "/api/v1/auth/permissions/{$permission->id}", $updateData, $admin);

        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['module_id']);
    }

    /** @test */
    public function it_can_get_active_permissions()
    {
        $admin = $this->createAdminUser();
        
        $module = Module::create([
            'name' => 'Test Module',
            'slug' => 'test-module',
            'order' => 1,
            'status' => true,
        ]);
        
        Permission::create([
            'name' => 'Active Permission 1',
            'slug' => 'active.1',
            'module_id' => $module->id,
            'status' => true,
        ]);
        
        Permission::create([
            'name' => 'Active Permission 2',
            'slug' => 'active.2',
            'module_id' => $module->id,
            'status' => true,
        ]);
        
        Permission::create([
            'name' => 'Inactive Permission',
            'slug' => 'inactive.1',
            'module_id' => $module->id,
            'status' => false,
        ]);

        $response = $this->authenticatedRequest('get', '/api/v1/auth/permissions/active', [], $admin);

        $this->assertSuccessResponse($response, 200);
        $data = $response->json();
        $this->assertCount(2, $data);
        $this->assertEquals('Active Permission 1', $data[0]['name']);
        $this->assertEquals('Active Permission 2', $data[1]['name']);
    }

    /** @test */
    public function it_can_get_permissions_by_module()
    {
        $admin = $this->createAdminUser();
        
        $module1 = Module::create([
            'name' => 'Module 1',
            'slug' => 'module-1',
            'order' => 1,
            'status' => true,
        ]);
        
        $module2 = Module::create([
            'name' => 'Module 2',
            'slug' => 'module-2',
            'order' => 2,
            'status' => true,
        ]);
        
        Permission::create([
            'name' => 'Permission Module 1',
            'slug' => 'module1.permission',
            'module_id' => $module1->id,
            'status' => true,
        ]);
        
        Permission::create([
            'name' => 'Permission Module 2',
            'slug' => 'module2.permission',
            'module_id' => $module2->id,
            'status' => true,
        ]);
        
        // Permiso inactivo en módulo 1
        Permission::create([
            'name' => 'Inactive Permission Module 1',
            'slug' => 'module1.inactive',
            'module_id' => $module1->id,
            'status' => false,
        ]);

        $response = $this->authenticatedRequest('get', "/api/v1/auth/permissions/by-module/{$module1->id}", [], $admin);

        $this->assertSuccessResponse($response, 200);
        $data = $response->json();
        $this->assertCount(1, $data); // Solo permisos activos
        $this->assertEquals('Permission Module 1', $data[0]['name']);
    }

    /** @test */
    public function it_can_bulk_create_permissions()
    {
        $admin = $this->createAdminUser();
        
        $module = Module::create([
            'name' => 'Test Module',
            'slug' => 'test-module',
            'order' => 1,
            'status' => true,
        ]);

        $bulkData = [
            'module_id' => $module->id,
            'permissions' => [
                [
                    'name' => 'View Users',
                    'slug' => 'users.view',
                    'description' => 'Can view users list'
                ],
                [
                    'name' => 'Create Users',
                    'slug' => 'users.create',
                    'description' => 'Can create new users'
                ],
                [
                    'name' => 'Edit Users',
                    'slug' => 'users.edit',
                    'description' => 'Can edit existing users'
                ]
            ]
        ];

        $response = $this->authenticatedRequest('post', '/api/v1/auth/permissions/bulk-create', $bulkData, $admin);

        $this->assertSuccessResponse($response, 201);
        $response->assertJson([
            'message' => 'Permissions created successfully'
        ]);
        
        $data = $response->json('data');
        $this->assertCount(3, $data);
        
        // Verificar que los permisos se crearon en la base de datos
        $this->assertDatabaseHas('permissions', [
            'name' => 'View Users',
            'slug' => 'users.view',
            'module_id' => $module->id
        ]);
        
        $this->assertDatabaseHas('permissions', [
            'name' => 'Create Users',
            'slug' => 'users.create',
            'module_id' => $module->id
        ]);
        
        $this->assertDatabaseHas('permissions', [
            'name' => 'Edit Users',
            'slug' => 'users.edit',
            'module_id' => $module->id
        ]);
    }

    /** @test */
    public function it_validates_bulk_create_permissions_data()
    {
        $admin = $this->createAdminUser();

        $bulkData = [
            'module_id' => 999, // ID inexistente
            'permissions' => [
                [
                    'name' => '',
                    'slug' => '',
                ]
            ]
        ];

        $response = $this->authenticatedRequest('post', '/api/v1/auth/permissions/bulk-create', $bulkData, $admin);

        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['module_id', 'permissions.0.name', 'permissions.0.slug']);
    }

    /** @test */
    public function it_requires_authentication_for_all_endpoints()
    {
        $response = $this->getJson('/api/v1/auth/permissions');
        $response->assertStatus(401);

        $response = $this->getJson('/api/v1/auth/permissions/1');
        $response->assertStatus(401);

        $response = $this->putJson('/api/v1/auth/permissions/1');
        $response->assertStatus(401);

        $response = $this->getJson('/api/v1/auth/permissions/active');
        $response->assertStatus(401);

        $response = $this->getJson('/api/v1/auth/permissions/by-module/1');
        $response->assertStatus(401);

        $response = $this->postJson('/api/v1/auth/permissions/bulk-create');
        $response->assertStatus(401);
    }
}