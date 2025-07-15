<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Kaely\AuthPackage\Models\User;
use Kaely\AuthPackage\Models\Role;
use Kaely\AuthPackage\Models\Permission;
use Kaely\AuthPackage\Models\Module;
use Kaely\AuthPackage\Models\RoleCategory;

class AuthPackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear módulos básicos
        $modules = [
            [
                'name' => 'Authentication',
                'slug' => 'auth',
                'order' => 1,
                'description' => 'Authentication and authorization module',
                'icon' => 'fas fa-shield-alt',
                'route' => '/auth',
                'is_active' => true,
            ],
            [
                'name' => 'Users',
                'slug' => 'users',
                'order' => 2,
                'description' => 'User management module',
                'icon' => 'fas fa-users',
                'route' => '/users',
                'is_active' => true,
            ],
            [
                'name' => 'Roles',
                'slug' => 'roles',
                'order' => 3,
                'description' => 'Role management module',
                'icon' => 'fas fa-user-tag',
                'route' => '/roles',
                'is_active' => true,
            ],
            [
                'name' => 'Permissions',
                'slug' => 'permissions',
                'order' => 4,
                'description' => 'Permission management module',
                'icon' => 'fas fa-key',
                'route' => '/permissions',
                'is_active' => true,
            ],
        ];

        foreach ($modules as $moduleData) {
            Module::create($moduleData);
        }   

        // Crear categorías de roles
        $roleCategories = [
            [
                'name' => 'System',
                'slug' => 'system',
                'description' => 'System level roles',
            ],
            [
                'name' => 'Administrative',
                'slug' => 'administrative',
                'description' => 'Administrative roles',
            ],
            [
                'name' => 'User',
                'slug' => 'user',
                'description' => 'Regular user roles',
            ],
        ];

        foreach ($roleCategories as $categoryData) {
            RoleCategory::create($categoryData);
        }

        // Crear roles
        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'description' => 'Super administrator with all permissions',
                'role_category_id' => RoleCategory::where('slug', 'system')->first()->id,
                'status' => true,
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Administrator with most permissions',
                'role_category_id' => RoleCategory::where('slug', 'administrative')->first()->id,
                'status' => true,
            ],
            [
                'name' => 'User',
                'slug' => 'user',
                'description' => 'Regular user with basic permissions',
                'role_category_id' => RoleCategory::where('slug', 'user')->first()->id,
                'status' => true,
            ],
        ];

        foreach ($roles as $roleData) {
            Role::create($roleData);
        }
        // Crear permisos
        $permissions = [
            // Auth permissions
            [
                'name' => 'Login',
                'slug' => 'auth.login',
                'description' => 'Can login to the system',
                'module_id' => Module::where('slug', 'auth')->first()->id,
                'status' => true,
            ],
            [
                'name' => 'Logout',
                'slug' => 'auth.logout',
                'description' => 'Can logout from the system',
                'module_id' => Module::where('slug', 'auth')->first()->id,
                'status' => true,
            ],
            [
                'name' => 'Register',
                'slug' => 'auth.register',
                'description' => 'Can register new users',
                'module_id' => Module::where('slug', 'auth')->first()->id,
                'status' => true,
            ],
            
            // User permissions
            [
                'name' => 'View Users',
                'slug' => 'users.view',
                'description' => 'Can view users',
                'module_id' => Module::where('slug', 'users')->first()->id,
                'status' => true,
            ],
            [
                'name' => 'Create Users',
                'slug' => 'users.create',
                'description' => 'Can create users',
                'module_id' => Module::where('slug', 'users')->first()->id,
                'status' => true,
            ],
            [
                'name' => 'Edit Users',
                'slug' => 'users.edit',
                'description' => 'Can edit users',
                'module_id' => Module::where('slug', 'users')->first()->id,
                'status' => true,
            ],
            [
                'name' => 'Delete Users',
                'slug' => 'users.delete',
                'description' => 'Can delete users',
                'module_id' => Module::where('slug', 'users')->first()->id,
                'status' => true,
            ],
            
            // Role permissions
            [
                'name' => 'View Roles',
                'slug' => 'roles.view',
                'description' => 'Can view roles',
                'module_id' => Module::where('slug', 'roles')->first()->id,
                'status' => true,
            ],
            [
                'name' => 'Create Roles',
                'slug' => 'roles.create',
                'description' => 'Can create roles',
                'module_id' => Module::where('slug', 'roles')->first()->id,
                'status' => true,
            ],
            [
                'name' => 'Edit Roles',
                'slug' => 'roles.edit',
                'description' => 'Can edit roles',
                'module_id' => Module::where('slug', 'roles')->first()->id,
                'status' => true,
            ],
            [
                'name' => 'Delete Roles',
                'slug' => 'roles.delete',
                'description' => 'Can delete roles',
                'module_id' => Module::where('slug', 'roles')->first()->id,
                'status' => true,
            ],
            
            // Permission permissions
            [
                'name' => 'View Permissions',
                'slug' => 'permissions.view',
                'description' => 'Can view permissions',
                'module_id' => Module::where('slug', 'permissions')->first()->id,
                'status' => true,
            ],
            [
                'name' => 'Create Permissions',
                'slug' => 'permissions.create',
                'description' => 'Can create permissions',
                'module_id' => Module::where('slug', 'permissions')->first()->id,
                'status' => true,
            ],
            [
                'name' => 'Edit Permissions',
                'slug' => 'permissions.edit',
                'description' => 'Can edit permissions',
                'module_id' => Module::where('slug', 'permissions')->first()->id,
                'status' => true,
            ],
            [
                'name' => 'Delete Permissions',
                'slug' => 'permissions.delete',
                'description' => 'Can delete permissions',
                'module_id' => Module::where('slug', 'permissions')->first()->id,
                'status' => true,
            ],
        ];

        foreach ($permissions as $permissionData) {
            Permission::create($permissionData);
        }

        // Asignar permisos a roles
        $superAdminRole = Role::where('slug', 'super-admin')->first();
        $adminRole = Role::where('slug', 'admin')->first();
        $userRole = Role::where('slug', 'user')->first();

        // Super Admin tiene todos los permisos
        $superAdminRole->permissions()->attach(Permission::all()->pluck('id'));

        // Admin tiene permisos de auth, users y roles
        $adminPermissions = Permission::whereIn('slug', [
            'auth.login', 'auth.logout', 'auth.register',
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
        ])->get();
        $adminRole->permissions()->attach($adminPermissions->pluck('id'));

        // User tiene permisos básicos
        $userPermissions = Permission::whereIn('slug', [
            'auth.login', 'auth.logout',
            'users.view',
        ])->get();
        $userRole->permissions()->attach($userPermissions->pluck('id'));

        // Crear usuario administrador por defecto
        $adminUser = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        // Asignar rol de super admin al usuario administrador
        $adminUser->roles()->attach($superAdminRole->id);
    }
} 