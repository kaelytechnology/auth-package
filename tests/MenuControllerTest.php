<?php

namespace Kaely\AuthPackage\Tests;

use Kaely\AuthPackage\Models\Module;
use Kaely\AuthPackage\Models\Permission;

class MenuControllerTest extends TestCase
{
    /** @test */
    public function it_can_get_dynamic_menu_for_authenticated_user()
    {
        $user = $this->createUser();
        $role = Role::where('slug', 'super-admin')->first();
        $user->roles()->attach($role->id);

        $response = $this->authenticatedRequest('get', '/api/v1/auth/menu', [], $user);

        $this->assertSuccessResponse($response, 200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'slug',
                    'description',
                    'icon',
                    'route',
                    'order',
                    'is_active',
                    'permissions',
                ]
            ]
        ]);
    }

    /** @test */
    public function it_requires_authentication_for_menu()
    {
        $response = $this->getJson('/api/v1/auth/menu');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_returns_only_active_modules_in_menu()
    {
        $user = $this->createUser();
        $role = Role::where('slug', 'super-admin')->first();
        $user->roles()->attach($role->id);

        // Crear módulo inactivo
        Module::create([
            'name' => 'Inactive Module',
            'slug' => 'inactive',
            'order' => 5,
            'description' => 'Inactive module',
            'icon' => 'fas fa-times',
            'route' => '/inactive',
            'is_active' => false,
        ]);

        $response = $this->authenticatedRequest('get', '/api/v1/auth/menu', [], $user);

        $this->assertSuccessResponse($response, 200);
        
        $modules = $response->json('data');
        foreach ($modules as $module) {
            $this->assertTrue($module['is_active']);
        }
    }

    /** @test */
    public function it_returns_modules_ordered_by_order_field()
    {
        $user = $this->createUser();
        $role = Role::where('slug', 'super-admin')->first();
        $user->roles()->attach($role->id);

        // Crear módulos con orden específico
        Module::create([
            'name' => 'Third Module',
            'slug' => 'third',
            'order' => 3,
            'description' => 'Third module',
            'icon' => 'fas fa-three',
            'route' => '/third',
            'is_active' => true,
        ]);

        Module::create([
            'name' => 'Second Module',
            'slug' => 'second',
            'order' => 2,
            'description' => 'Second module',
            'icon' => 'fas fa-two',
            'route' => '/second',
            'is_active' => true,
        ]);

        $response = $this->authenticatedRequest('get', '/api/v1/auth/menu', [], $user);

        $this->assertSuccessResponse($response, 200);
        
        $modules = $response->json('data');
        $this->assertGreaterThan(0, count($modules));
        
        // Verificar que están ordenados
        for ($i = 0; $i < count($modules) - 1; $i++) {
            $this->assertLessThanOrEqual($modules[$i + 1]['order'], $modules[$i]['order']);
        }
    }

    /** @test */
    public function it_can_get_user_permissions()
    {
        $user = $this->createUser();
        $role = Role::where('slug', 'super-admin')->first();
        $user->roles()->attach($role->id);

        $response = $this->authenticatedRequest('get', '/api/v1/auth/menu/permissions', [], $user);

        $this->assertSuccessResponse($response, 200);
        $response->assertJsonStructure([
            'data' => [
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
    public function it_requires_authentication_for_permissions()
    {
        $response = $this->getJson('/api/v1/auth/menu/permissions');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_check_if_user_has_specific_permission()
    {
        $user = $this->createUser();
        $role = Role::where('slug', 'super-admin')->first();
        $user->roles()->attach($role->id);

        $checkData = [
            'permission' => 'auth.login',
        ];

        $response = $this->authenticatedRequest('post', '/api/v1/auth/menu/has-permission', $checkData, $user);

        $this->assertSuccessResponse($response, 200);
        $response->assertJsonStructure([
            'data' => [
                'has_permission',
                'permission',
            ]
        ]);
        
        $this->assertTrue($response->json('data.has_permission'));
    }

    /** @test */
    public function it_returns_false_for_nonexistent_permission()
    {
        $user = $this->createUser();
        $role = Role::where('slug', 'super-admin')->first();
        $user->roles()->attach($role->id);

        $checkData = [
            'permission' => 'nonexistent.permission',
        ];

        $response = $this->authenticatedRequest('post', '/api/v1/auth/menu/has-permission', $checkData, $user);

        $this->assertSuccessResponse($response, 200);
        $this->assertFalse($response->json('data.has_permission'));
    }

    /** @test */
    public function it_validates_permission_parameter()
    {
        $user = $this->createUser();

        $response = $this->authenticatedRequest('post', '/api/v1/auth/menu/has-permission', [], $user);

        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['permission']);
    }

    /** @test */
    public function it_can_check_if_user_has_any_of_specified_permissions()
    {
        $user = $this->createUser();
        $role = Role::where('slug', 'super-admin')->first();
        $user->roles()->attach($role->id);

        $checkData = [
            'permissions' => ['auth.login', 'auth.logout', 'nonexistent.permission'],
        ];

        $response = $this->authenticatedRequest('post', '/api/v1/auth/menu/has-any-permission', $checkData, $user);

        $this->assertSuccessResponse($response, 200);
        $response->assertJsonStructure([
            'data' => [
                'has_permission',
                'permissions',
                'matched_permissions',
            ]
        ]);
        
        $this->assertTrue($response->json('data.has_permission'));
        $this->assertNotEmpty($response->json('data.matched_permissions'));
    }

    /** @test */
    public function it_returns_false_when_user_has_none_of_specified_permissions()
    {
        $user = $this->createUser();
        $role = Role::where('slug', 'super-admin')->first();
        $user->roles()->attach($role->id);

        $checkData = [
            'permissions' => ['nonexistent.permission1', 'nonexistent.permission2'],
        ];

        $response = $this->authenticatedRequest('post', '/api/v1/auth/menu/has-any-permission', $checkData, $user);

        $this->assertSuccessResponse($response, 200);
        $this->assertFalse($response->json('data.has_permission'));
        $this->assertEmpty($response->json('data.matched_permissions'));
    }

    /** @test */
    public function it_validates_permissions_parameter()
    {
        $user = $this->createUser();

        $response = $this->authenticatedRequest('post', '/api/v1/auth/menu/has-any-permission', [], $user);

        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['permissions']);
    }

    /** @test */
    public function it_can_get_user_modules()
    {
        $user = $this->createUser();
        $role = Role::where('slug', 'super-admin')->first();
        $user->roles()->attach($role->id);

        $response = $this->authenticatedRequest('get', '/api/v1/auth/menu/modules', [], $user);

        $this->assertSuccessResponse($response, 200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'slug',
                    'description',
                    'icon',
                    'route',
                    'order',
                    'is_active',
                ]
            ]
        ]);
    }

    /** @test */
    public function it_requires_authentication_for_modules()
    {
        $response = $this->getJson('/api/v1/auth/menu/modules');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_returns_only_modules_user_has_access_to()
    {
        // Crear usuario sin rol de super admin
        $user = $this->createUser(['email' => 'limited@example.com']);
        
        // Crear rol con permisos limitados
        $limitedRole = Role::create([
            'name' => 'Limited Role',
            'slug' => 'limited-role',
            'description' => 'Limited role',
            'role_category_id' => 1,
            'status' => true,
        ]);
        
        // Asignar solo permiso de login
        $loginPermission = Permission::where('slug', 'auth.login')->first();
        $limitedRole->permissions()->attach($loginPermission->id);
        
        $user->roles()->attach($limitedRole->id);

        $response = $this->authenticatedRequest('get', '/api/v1/auth/menu', [], $user);

        $this->assertSuccessResponse($response, 200);
        
        $modules = $response->json('data');
        // Debería tener acceso solo al módulo de auth
        $this->assertCount(1, $modules);
        $this->assertEquals('auth', $modules[0]['slug']);
    }

    /** @test */
    public function it_returns_empty_menu_for_user_without_permissions()
    {
        $user = $this->createUser(['email' => 'noperms@example.com']);
        
        // No asignar ningún rol al usuario

        $response = $this->authenticatedRequest('get', '/api/v1/auth/menu', [], $user);

        $this->assertSuccessResponse($response, 200);
        $this->assertEmpty($response->json('data'));
    }

    /** @test */
    public function it_returns_empty_permissions_for_user_without_roles()
    {
        $user = $this->createUser(['email' => 'noperms@example.com']);
        
        // No asignar ningún rol al usuario

        $response = $this->authenticatedRequest('get', '/api/v1/auth/menu/permissions', [], $user);

        $this->assertSuccessResponse($response, 200);
        $this->assertEmpty($response->json('data'));
    }

    /** @test */
    public function it_returns_false_for_permission_check_when_user_has_no_roles()
    {
        $user = $this->createUser(['email' => 'noperms@example.com']);
        
        // No asignar ningún rol al usuario

        $checkData = [
            'permission' => 'auth.login',
        ];

        $response = $this->authenticatedRequest('post', '/api/v1/auth/menu/has-permission', $checkData, $user);

        $this->assertSuccessResponse($response, 200);
        $this->assertFalse($response->json('data.has_permission'));
    }

    /** @test */
    public function it_returns_false_for_any_permission_check_when_user_has_no_roles()
    {
        $user = $this->createUser(['email' => 'noperms@example.com']);
        
        // No asignar ningún rol al usuario

        $checkData = [
            'permissions' => ['auth.login', 'auth.logout'],
        ];

        $response = $this->authenticatedRequest('post', '/api/v1/auth/menu/has-any-permission', $checkData, $user);

        $this->assertSuccessResponse($response, 200);
        $this->assertFalse($response->json('data.has_permission'));
        $this->assertEmpty($response->json('data.matched_permissions'));
    }
} 