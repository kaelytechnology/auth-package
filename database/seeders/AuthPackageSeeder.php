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
                'code' => 'auth',
                'description' => 'Authentication and authorization module',
                'icon' => 'fas fa-shield-alt',
                'route' => '/auth',
                'order' => 1,
                'status' => true,
            ],
            [
                'name' => 'Users',
                'code' => 'users',
                'description' => 'User management module',
                'icon' => 'fas fa-users',
                'route' => '/users',
                'order' => 2,
                'status' => true,
            ],
            [
                'name' => 'Roles',
                'code' => 'roles',
                'description' => 'Role management module',
                'icon' => 'fas fa-user-tag',
                'route' => '/roles',
                'order' => 3,
                'status' => true,
            ],
            [
                'name' => 'Permissions',
                'code' => 'permissions',
                'description' => 'Permission management module',
                'icon' => 'fas fa-key',
                'route' => '/permissions',
                'order' => 4,
                'status' => true,
            ],
        ];

        foreach ($modules as $moduleData) {
            Module::create($moduleData);
        }

        // Crear categorías de roles
        $roleCategories = [
            [
                'name' => 'System',
                'code' => 'system',
                'description' => 'System level roles',
            ],
            [
                'name' => 'Administrative',
                'code' => 'administrative',
                'description' => 'Administrative roles',
            ],
            [
                'name' => 'User',
                'code' => 'user',
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
                'code' => 'super-admin',
                'description' => 'Super administrator with all permissions',
                'role_category_id' => RoleCategory::where('code', 'system')->first()->id,
                'status' => true,
            ],
            [
                'name' => 'Admin',
                'code' => 'admin',
                'description' => 'Administrator with most permissions',
                'role_category_id' => RoleCategory::where('code', 'administrative')->first()->id,
                'status' => true,
            ],
            [
                'name' => 'User',
                'code' => 'user',
                'description' => 'Regular user with basic permissions',
                'role_category_id' => RoleCategory::where('code', 'user')->first()->id,
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
                'code' => 'auth.login',
                'description' => 'Can login to the system',
                'module_id' => Module::where('code', 'auth')->first()->id,
                'status' => true,
            ],
            [
                'name' => 'Logout',
                'code' => 'auth.logout',
                'description' => 'Can logout from the system',
                'module_id' => Module::where('code', 'auth')->first()->id,
                'status' => true,
            ],
            [
                'name' => 'Register',
                'code' => 'auth.register',
                'description' => 'Can register new users',
                'module_id' => Module::where('code', 'auth')->first()->id,
                'status' => true,
            ],
            
            // User permissions
            [
                'name' => 'View Users',
                'code' => 'users.view',
                'description' => 'Can view users',
                'module_id' => Module::where('code', 'users')->first()->id,
                'status' => true,
            ],
            [
                'name' => 'Create Users',
                'code' => 'users.create',
                'description' => 'Can create users',
                'module_id' => Module::where('code', 'users')->first()->id,
                'status' => true,
            ],
            [
                'name' => 'Edit Users',
                'code' => 'users.edit',
                'description' => 'Can edit users',
                'module_id' => Module::where('code', 'users')->first()->id,
                'status' => true,
            ],
            [
                'name' => 'Delete Users',
                'code' => 'users.delete',
                'description' => 'Can delete users',
                'module_id' => Module::where('code', 'users')->first()->id,
                'status' => true,
            ],
            
            // Role permissions
            [
                'name' => 'View Roles',
                'code' => 'roles.view',
                'description' => 'Can view roles',
                'module_id' => Module::where('code', 'roles')->first()->id,
                'status' => true,
            ],
            [
                'name' => 'Create Roles',
                'code' => 'roles.create',
                'description' => 'Can create roles',
                'module_id' => Module::where('code', 'roles')->first()->id,
                'status' => true,
            ],
            [
                'name' => 'Edit Roles',
                'code' => 'roles.edit',
                'description' => 'Can edit roles',
                'module_id' => Module::where('code', 'roles')->first()->id,
                'status' => true,
            ],
            [
                'name' => 'Delete Roles',
                'code' => 'roles.delete',
                'description' => 'Can delete roles',
                'module_id' => Module::where('code', 'roles')->first()->id,
                'status' => true,
            ],
            
            // Permission permissions
            [
                'name' => 'View Permissions',
                'code' => 'permissions.view',
                'description' => 'Can view permissions',
                'module_id' => Module::where('code', 'permissions')->first()->id,
                'status' => true,
            ],
            [
                'name' => 'Create Permissions',
                'code' => 'permissions.create',
                'description' => 'Can create permissions',
                'module_id' => Module::where('code', 'permissions')->first()->id,
                'status' => true,
            ],
            [
                'name' => 'Edit Permissions',
                'code' => 'permissions.edit',
                'description' => 'Can edit permissions',
                'module_id' => Module::where('code', 'permissions')->first()->id,
                'status' => true,
            ],
            [
                'name' => 'Delete Permissions',
                'code' => 'permissions.delete',
                'description' => 'Can delete permissions',
                'module_id' => Module::where('code', 'permissions')->first()->id,
                'status' => true,
            ],
        ];

        foreach ($permissions as $permissionData) {
            Permission::create($permissionData);
        }

        // Asignar permisos a roles
        $superAdminRole = Role::where('code', 'super-admin')->first();
        $adminRole = Role::where('code', 'admin')->first();
        $userRole = Role::where('code', 'user')->first();

        // Super Admin tiene todos los permisos
        $superAdminRole->permissions()->attach(Permission::all()->pluck('id'));

        // Admin tiene permisos de auth, users y roles
        $adminPermissions = Permission::whereIn('code', [
            'auth.login', 'auth.logout', 'auth.register',
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
        ])->get();
        $adminRole->permissions()->attach($adminPermissions->pluck('id'));

        // User tiene permisos básicos
        $userPermissions = Permission::whereIn('code', [
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