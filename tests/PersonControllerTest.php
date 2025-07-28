<?php

namespace Kaely\AuthPackage\Tests;

use Kaely\AuthPackage\Models\Person;
use Kaely\AuthPackage\Models\User;
use Illuminate\Support\Facades\Hash;

class PersonControllerTest extends TestCase
{
    /** @test */
    public function it_can_list_people()
    {
        $admin = $this->createAdminUser();
        
        // Crear usuarios adicionales con personas
        $user1 = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'is_active' => true
        ]);
        
        Person::create([
            'user_id' => $user1->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+1234567890',
            'gender' => 'male'
        ]);
        
        $user2 = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password'),
            'is_active' => true
        ]);
        
        Person::create([
            'user_id' => $user2->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'phone' => '+0987654321',
            'gender' => 'female'
        ]);

        $response = $this->authenticatedRequest('get', '/api/v1/auth/people', [], $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJsonStructure([
            'data' => [
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'first_name',
                        'last_name',
                        'full_name',
                        'phone',
                        'gender',
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
    public function it_requires_authentication_to_list_people()
    {
        $response = $this->getJson('/api/v1/auth/people');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_create_person()
    {
        $admin = $this->createAdminUser();
        
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'is_active' => true
        ]);

        $personData = [
            'user_id' => $user->id,
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone' => '+1234567890',
            'address' => '123 Test Street',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
        ];

        $response = $this->authenticatedRequest('post', '/api/v1/auth/people', $personData, $admin);

        $this->assertSuccessResponse($response, 201);
        $response->assertJson([
            'data' => [
                'first_name' => 'Test',
                'last_name' => 'User',
                'full_name' => 'Test User',
                'phone' => '+1234567890',
                'gender' => 'male',
            ]
        ]);

        $this->assertDatabaseHas('people', [
            'user_id' => $user->id,
            'first_name' => 'Test',
            'last_name' => 'User',
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_person()
    {
        $admin = $this->createAdminUser();

        $response = $this->authenticatedRequest('post', '/api/v1/auth/people', [], $admin);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['user_id', 'first_name', 'last_name']);
    }

    /** @test */
    public function it_validates_unique_user_id_when_creating_person()
    {
        $admin = $this->createAdminUser();
        
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'is_active' => true
        ]);
        
        // Crear primera persona
        Person::create([
            'user_id' => $user->id,
            'first_name' => 'First',
            'last_name' => 'Person'
        ]);

        // Intentar crear segunda persona para el mismo usuario
        $personData = [
            'user_id' => $user->id,
            'first_name' => 'Second',
            'last_name' => 'Person',
        ];

        $response = $this->authenticatedRequest('post', '/api/v1/auth/people', $personData, $admin);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['user_id']);
    }

    /** @test */
    public function it_can_show_person()
    {
        $admin = $this->createAdminUser();
        
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'is_active' => true
        ]);
        
        $person = Person::create([
            'user_id' => $user->id,
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone' => '+1234567890'
        ]);

        $response = $this->authenticatedRequest('get', "/api/v1/auth/people/{$person->id}", [], $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJson([
            'data' => [
                'id' => $person->id,
                'first_name' => 'Test',
                'last_name' => 'User',
                'phone' => '+1234567890',
            ]
        ]);
    }

    /** @test */
    public function it_can_update_person()
    {
        $admin = $this->createAdminUser();
        
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'is_active' => true
        ]);
        
        $person = Person::create([
            'user_id' => $user->id,
            'first_name' => 'Original',
            'last_name' => 'Name'
        ]);

        $updateData = [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'phone' => '+1234567890',
            'address' => '123 Updated Street',
            'gender' => 'female',
        ];

        $response = $this->authenticatedRequest('put', "/api/v1/auth/people/{$person->id}", $updateData, $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJson([
            'data' => [
                'first_name' => 'Updated',
                'last_name' => 'Name',
                'phone' => '+1234567890',
                'gender' => 'female',
            ]
        ]);

        $this->assertDatabaseHas('people', [
            'id' => $person->id,
            'first_name' => 'Updated',
            'last_name' => 'Name',
        ]);
    }

    /** @test */
    public function it_can_delete_person()
    {
        $admin = $this->createAdminUser();
        
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'is_active' => true
        ]);
        
        $person = Person::create([
            'user_id' => $user->id,
            'first_name' => 'Deletable',
            'last_name' => 'Person'
        ]);

        $response = $this->authenticatedRequest('delete', "/api/v1/auth/people/{$person->id}", [], $admin);

        $this->assertSuccessResponse($response, 200);
        $this->assertSoftDeleted('people', ['id' => $person->id]);
    }

    /** @test */
    public function it_can_get_person_by_user()
    {
        $admin = $this->createAdminUser();
        
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'is_active' => true
        ]);
        
        $person = Person::create([
            'user_id' => $user->id,
            'first_name' => 'Test',
            'last_name' => 'User'
        ]);

        $response = $this->authenticatedRequest('get', "/api/v1/auth/users/{$user->id}/person", [], $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJson([
            'data' => [
                'id' => $person->id,
                'user_id' => $user->id,
                'first_name' => 'Test',
                'last_name' => 'User',
            ]
        ]);
    }

    /** @test */
    public function it_returns_404_when_user_has_no_person()
    {
        $admin = $this->createAdminUser();
        
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'is_active' => true
        ]);

        $response = $this->authenticatedRequest('get', "/api/v1/auth/users/{$user->id}/person", [], $admin);

        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'Person not found for this user'
        ]);
    }

    /** @test */
    public function it_can_create_person_for_user()
    {
        $admin = $this->createAdminUser();
        
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'is_active' => true
        ]);

        $personData = [
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone' => '+1234567890',
            'gender' => 'male',
        ];

        $response = $this->authenticatedRequest('post', "/api/v1/auth/users/{$user->id}/person", $personData, $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJson([
            'message' => 'Person created successfully',
            'data' => [
                'user_id' => $user->id,
                'first_name' => 'Test',
                'last_name' => 'User',
            ]
        ]);

        $this->assertDatabaseHas('people', [
            'user_id' => $user->id,
            'first_name' => 'Test',
            'last_name' => 'User',
        ]);
    }

    /** @test */
    public function it_can_update_existing_person_for_user()
    {
        $admin = $this->createAdminUser();
        
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'is_active' => true
        ]);
        
        // Crear persona existente
        Person::create([
            'user_id' => $user->id,
            'first_name' => 'Original',
            'last_name' => 'Name'
        ]);

        $updateData = [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'phone' => '+1234567890',
        ];

        $response = $this->authenticatedRequest('post', "/api/v1/auth/users/{$user->id}/person", $updateData, $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJson([
            'message' => 'Person updated successfully',
            'data' => [
                'first_name' => 'Updated',
                'last_name' => 'Name',
                'phone' => '+1234567890',
            ]
        ]);

        $this->assertDatabaseHas('people', [
            'user_id' => $user->id,
            'first_name' => 'Updated',
            'last_name' => 'Name',
        ]);
    }

    /** @test */
    public function it_can_get_people_statistics()
    {
        $admin = $this->createAdminUser();
        
        // Crear usuarios y personas para estadísticas
        $users = [];
        for ($i = 1; $i <= 3; $i++) {
            $users[] = User::create([
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'password' => Hash::make('password'),
                'is_active' => true
            ]);
        }
        
        Person::create([
            'user_id' => $users[0]->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'gender' => 'male',
            'phone' => '+1234567890'
        ]);
        
        Person::create([
            'user_id' => $users[1]->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'gender' => 'female',
            'birth_date' => '1990-01-01'
        ]);
        
        Person::create([
            'user_id' => $users[2]->id,
            'first_name' => 'Alex',
            'last_name' => 'Johnson',
            'gender' => 'other',
            'address' => '123 Main St'
        ]);

        $response = $this->authenticatedRequest('get', '/api/v1/auth/people/statistics', [], $admin);

        $this->assertSuccessResponse($response, 200);
        $response->assertJsonStructure([
            'total_people',
            'by_gender' => [
                'male',
                'female',
                'other',
                'not_specified'
            ],
            'with_phone',
            'with_address',
            'with_birth_date'
        ]);
        
        $response->assertJson([
            'total_people' => 3,
            'by_gender' => [
                'male' => 1,
                'female' => 1,
                'other' => 1,
                'not_specified' => 0
            ],
            'with_phone' => 1,
            'with_address' => 1,
            'with_birth_date' => 1
        ]);
    }
}