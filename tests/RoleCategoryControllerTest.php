<?php

namespace Kaely\AuthPackage\Tests;

use Kaely\AuthPackage\Models\RoleCategory;
use Kaely\AuthPackage\Models\Role;

class RoleCategoryControllerTest extends TestCase
{
    /** @test */
    public function it_can_list_role_categories()
    {
        $admin = $this->createAdminUser();
        
        // Crear categorías adicionales
        RoleCategory::create([
            'name' => 'Administrative',
            'slug' => 'administrative',
            'description' => 'Administrative roles category'
        ]);
        
        RoleCategory::create([
            'name' => 'User Level',
            'slug' => 'user-level',
            'description' => 'User level roles category'
        ]);

        $response = $this->authenticatedRequest('get', '/api/v1/auth/role-categories', [], $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJsonStructure([
            'data' => [
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'description',
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
    public function it_requires_authentication_to_list_role_categories()
    {
        $response = $this->getJson('/api/v1/auth/role-categories');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_create_role_category()
    {
        $admin = $this->createAdminUser();

        $categoryData = [
            'name' => 'New Category',
            'slug' => 'new-category',
            'description' => 'A new category for testing',
        ];

        $response = $this->authenticatedRequest('post', '/api/v1/auth/role-categories', $categoryData, $admin);

        $this->assertSuccessResponse($response, 201);
        $response->assertJson([
            'data' => [
                'name' => 'New Category',
                'slug' => 'new-category',
                'description' => 'A new category for testing',
            ]
        ]);

        $this->assertDatabaseHas('role_categories', [
            'name' => 'New Category',
            'slug' => 'new-category',
        ]);
    }

    /** @test */
    public function it_auto_generates_slug_when_not_provided()
    {
        $admin = $this->createAdminUser();

        $categoryData = [
            'name' => 'Auto Slug Category',
            'description' => 'Category with auto-generated slug',
        ];

        $response = $this->authenticatedRequest('post', '/api/v1/auth/role-categories', $categoryData, $admin);

        $this->assertSuccessResponse($response, 201);
        $response->assertJson([
            'data' => [
                'name' => 'Auto Slug Category',
                'slug' => 'auto-slug-category',
            ]
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_role_category()
    {
        $admin = $this->createAdminUser();

        $response = $this->authenticatedRequest('post', '/api/v1/auth/role-categories', [], $admin);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function it_can_show_role_category()
    {
        $admin = $this->createAdminUser();
        $category = RoleCategory::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test description'
        ]);

        $response = $this->authenticatedRequest('get', "/api/v1/auth/role-categories/{$category->id}", [], $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJson([
            'data' => [
                'id' => $category->id,
                'name' => 'Test Category',
                'slug' => 'test-category',
            ]
        ]);
    }

    /** @test */
    public function it_can_update_role_category()
    {
        $admin = $this->createAdminUser();
        $category = RoleCategory::create([
            'name' => 'Original Category',
            'slug' => 'original-category',
            'description' => 'Original description'
        ]);

        $updateData = [
            'name' => 'Updated Category',
            'slug' => 'updated-category',
            'description' => 'Updated description',
        ];

        $response = $this->authenticatedRequest('put', "/api/v1/auth/role-categories/{$category->id}", $updateData, $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJson([
            'data' => [
                'name' => 'Updated Category',
                'slug' => 'updated-category',
                'description' => 'Updated description',
            ]
        ]);

        $this->assertDatabaseHas('role_categories', [
            'id' => $category->id,
            'name' => 'Updated Category',
            'slug' => 'updated-category',
        ]);
    }

    /** @test */
    public function it_can_delete_role_category_without_roles()
    {
        $admin = $this->createAdminUser();
        $category = RoleCategory::create([
            'name' => 'Deletable Category',
            'slug' => 'deletable-category',
            'description' => 'Category to be deleted'
        ]);

        $response = $this->authenticatedRequest('delete', "/api/v1/auth/role-categories/{$category->id}", [], $admin);

        $this->assertSuccessResponse($response, 200);
        $this->assertSoftDeleted('role_categories', ['id' => $category->id]);
    }

    /** @test */
    public function it_cannot_delete_role_category_with_associated_roles()
    {
        $admin = $this->createAdminUser();
        $category = RoleCategory::create([
            'name' => 'Category with Roles',
            'slug' => 'category-with-roles',
            'description' => 'Category that has roles'
        ]);

        // Crear un rol asociado a la categoría
        Role::create([
            'name' => 'Test Role',
            'slug' => 'test-role',
            'role_category_id' => $category->id,
            'status' => true
        ]);

        $response = $this->authenticatedRequest('delete', "/api/v1/auth/role-categories/{$category->id}", [], $admin);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'Cannot delete role category with associated roles'
        ]);

        $this->assertDatabaseHas('role_categories', ['id' => $category->id]);
    }

    /** @test */
    public function it_can_get_active_role_categories()
    {
        $admin = $this->createAdminUser();
        
        RoleCategory::create([
            'name' => 'Active Category 1',
            'slug' => 'active-category-1',
            'description' => 'First active category'
        ]);
        
        RoleCategory::create([
            'name' => 'Active Category 2',
            'slug' => 'active-category-2',
            'description' => 'Second active category'
        ]);

        $response = $this->authenticatedRequest('get', '/api/v1/auth/role-categories/active', [], $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'name',
                'slug',
                'description'
            ]
        ]);
    }

    /** @test */
    public function it_can_get_roles_by_category()
    {
        $admin = $this->createAdminUser();
        $category = RoleCategory::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Category for testing roles'
        ]);

        // Crear roles asociados a la categoría
        Role::create([
            'name' => 'Role 1',
            'slug' => 'role-1',
            'role_category_id' => $category->id,
            'status' => true
        ]);
        
        Role::create([
            'name' => 'Role 2',
            'slug' => 'role-2',
            'role_category_id' => $category->id,
            'status' => true
        ]);

        $response = $this->authenticatedRequest('get', "/api/v1/auth/role-categories/{$category->id}/roles", [], $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJsonStructure([
            'category' => [
                'id',
                'name',
                'slug',
                'description'
            ],
            'roles' => [
                '*' => [
                    'id',
                    'name',
                    'slug',
                    'status'
                ]
            ]
        ]);
    }
}