<?php

namespace Kaely\AuthPackage\Tests;

use Kaely\AuthPackage\Models\Module;
use Kaely\AuthPackage\Models\User;
use Kaely\AuthPackage\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class ModuleControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_modules()
    {
        $admin = $this->createAdminUser();
        
        // Crear algunos módulos de prueba
        Module::create([
            'name' => 'Test Module 1',
            'slug' => 'test-module-1',
            'description' => 'Test module description',
            'icon' => 'fas fa-test',
            'route' => '/test1',
            'order' => 1,
            'status' => true,
        ]);
        
        Module::create([
            'name' => 'Test Module 2',
            'slug' => 'test-module-2',
            'description' => 'Another test module',
            'icon' => 'fas fa-test2',
            'route' => '/test2',
            'order' => 2,
            'status' => false,
        ]);

        $response = $this->authenticatedRequest('get', '/api/v1/auth/modules', [], $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJsonStructure([
            'data',
            'links',
            'meta'
        ]);
    }

    /** @test */
    public function it_can_search_modules()
    {
        $admin = $this->createAdminUser();
        
        Module::create([
            'name' => 'Authentication Module',
            'slug' => 'auth-module',
            'description' => 'Authentication functionality',
            'order' => 1,
            'status' => true,
        ]);
        
        Module::create([
            'name' => 'User Management',
            'slug' => 'user-management',
            'description' => 'User management functionality',
            'order' => 2,
            'status' => true,
        ]);

        $response = $this->authenticatedRequest('get', '/api/v1/auth/modules?search=auth', [], $admin);

        $this->assertSuccessResponse($response, 200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Authentication Module', $data[0]['name']);
    }

    /** @test */
    public function it_can_filter_modules_by_status()
    {
        $admin = $this->createAdminUser();
        
        Module::create([
            'name' => 'Active Module',
            'slug' => 'active-module',
            'status' => true,
            'order' => 1,
        ]);
        
        Module::create([
            'name' => 'Inactive Module',
            'slug' => 'inactive-module',
            'status' => false,
            'order' => 2,
        ]);

        $response = $this->authenticatedRequest('get', '/api/v1/auth/modules?status=1', [], $admin);

        $this->assertSuccessResponse($response, 200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Active Module', $data[0]['name']);
    }

    /** @test */
    public function it_can_show_specific_module()
    {
        $admin = $this->createAdminUser();
        
        $module = Module::create([
            'name' => 'Test Module',
            'slug' => 'test-module',
            'description' => 'Test module description',
            'icon' => 'fas fa-test',
            'route' => '/test',
            'order' => 1,
            'status' => true,
        ]);

        $response = $this->authenticatedRequest('get', "/api/v1/auth/modules/{$module->id}", [], $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'slug',
                'description',
                'icon',
                'route',
                'order',
                'status'
            ]
        ]);
        $this->assertEquals('Test Module', $response->json('data.name'));
    }

    /** @test */
    public function it_can_update_module()
    {
        $admin = $this->createAdminUser();
        
        $module = Module::create([
            'name' => 'Original Module',
            'slug' => 'original-module',
            'description' => 'Original description',
            'order' => 1,
            'status' => true,
        ]);

        $updateData = [
            'name' => 'Updated Module',
            'slug' => 'updated-module',
            'description' => 'Updated description',
            'icon' => 'fas fa-updated',
            'route' => '/updated',
            'order' => 2,
            'status' => false,
        ];

        $response = $this->authenticatedRequest('put', "/api/v1/auth/modules/{$module->id}", $updateData, $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJson([
            'message' => 'Module updated successfully'
        ]);
        
        $module->refresh();
        $this->assertEquals('Updated Module', $module->name);
        $this->assertEquals('updated-module', $module->slug);
        $this->assertEquals('Updated description', $module->description);
    }

    /** @test */
    public function it_validates_required_fields_when_updating_module()
    {
        $admin = $this->createAdminUser();
        
        $module = Module::create([
            'name' => 'Test Module',
            'slug' => 'test-module',
            'order' => 1,
            'status' => true,
        ]);

        $updateData = [
            'name' => '',
            'slug' => '',
        ];

        $response = $this->authenticatedRequest('put', "/api/v1/auth/modules/{$module->id}", $updateData, $admin);

        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['name', 'slug']);
    }

    /** @test */
    public function it_validates_unique_slug_when_updating_module()
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

        $updateData = [
            'name' => 'Updated Module 2',
            'slug' => 'module-1', // Slug ya existe
        ];

        $response = $this->authenticatedRequest('put', "/api/v1/auth/modules/{$module2->id}", $updateData, $admin);

        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['slug']);
    }

    /** @test */
    public function it_can_get_active_modules()
    {
        $admin = $this->createAdminUser();
        
        Module::create([
            'name' => 'Active Module 1',
            'slug' => 'active-1',
            'status' => true,
            'order' => 1,
        ]);
        
        Module::create([
            'name' => 'Active Module 2',
            'slug' => 'active-2',
            'status' => true,
            'order' => 2,
        ]);
        
        Module::create([
            'name' => 'Inactive Module',
            'slug' => 'inactive',
            'status' => false,
            'order' => 3,
        ]);

        $response = $this->authenticatedRequest('get', '/api/v1/auth/modules/active', [], $admin);

        $this->assertSuccessResponse($response, 200);
        $data = $response->json();
        $this->assertCount(2, $data);
        $this->assertEquals('Active Module 1', $data[0]['name']);
        $this->assertEquals('Active Module 2', $data[1]['name']);
    }

    /** @test */
    public function it_can_update_module_order()
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
        
        $module3 = Module::create([
            'name' => 'Module 3',
            'slug' => 'module-3',
            'order' => 3,
            'status' => true,
        ]);

        $orderData = [
            'modules' => [
                ['id' => $module1->id, 'order' => 3],
                ['id' => $module2->id, 'order' => 1],
                ['id' => $module3->id, 'order' => 2],
            ]
        ];

        $response = $this->authenticatedRequest('post', '/api/v1/auth/modules/update-order', $orderData, $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJson([
            'message' => 'Module order updated successfully'
        ]);
        
        // Verificar que el orden se actualizó
        $module1->refresh();
        $module2->refresh();
        $module3->refresh();
        
        $this->assertEquals(3, $module1->order);
        $this->assertEquals(1, $module2->order);
        $this->assertEquals(2, $module3->order);
    }

    /** @test */
    public function it_validates_module_order_update_data()
    {
        $admin = $this->createAdminUser();

        $orderData = [
            'modules' => [
                ['id' => 999, 'order' => 1], // ID inexistente
            ]
        ];

        $response = $this->authenticatedRequest('post', '/api/v1/auth/modules/update-order', $orderData, $admin);

        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['modules.0.id']);
    }

    /** @test */
    public function it_requires_authentication_for_all_endpoints()
    {
        $response = $this->getJson('/api/v1/auth/modules');
        $response->assertStatus(401);

        $response = $this->getJson('/api/v1/auth/modules/1');
        $response->assertStatus(401);

        $response = $this->putJson('/api/v1/auth/modules/1');
        $response->assertStatus(401);

        $response = $this->getJson('/api/v1/auth/modules/active');
        $response->assertStatus(401);

        $response = $this->postJson('/api/v1/auth/modules/update-order');
        $response->assertStatus(401);
    }
}