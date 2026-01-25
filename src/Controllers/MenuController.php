<?php

namespace Kaely\AuthPackage\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Kaely\AuthPackage\Models\Module;
use Kaely\AuthPackage\Models\Permission;

class MenuController extends Controller
{
    /**
     * Get dynamic menu for authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated'
            ], 401);
        }

        // Obtener módulos activos y permisos del usuario
        $modules = Module::where('is_active', true)
            ->with('children')
            ->orderBy('order')
            ->get();

        $userPermissions = $user->getAllPermissions();
        $modulePermissionsMap = [];
        $permittedModuleIds = [];
        foreach ($userPermissions as $perm) {
            $modulePermissionsMap[$perm->module_id][] = $perm->slug;
            $permittedModuleIds[] = $perm->module_id;
        }
        $permittedModuleIds = array_unique($permittedModuleIds);

        // Identificar todos los módulos visibles (directos + ancestros)
        $visibleModuleIds = [];
        
        // Función auxiliar para agregar módulo y sus padres recursivamente
        $addModuleAndAncestors = function($moduleId) use (&$visibleModuleIds, $modules, &$addModuleAndAncestors) {
            if (in_array($moduleId, $visibleModuleIds)) {
                return; // Ya procesado
            }
            
            $module = $modules->firstWhere('id', $moduleId);
            if (!$module) {
                return;
            }
            
            $visibleModuleIds[] = $moduleId;
            
            if ($module->parent_id) {
                $addModuleAndAncestors($module->parent_id);
            }
        };

        foreach ($permittedModuleIds as $moduleId) {
            $addModuleAndAncestors($moduleId);
        }

        // Filtrar módulos a los que el usuario tiene acceso (directo o por herencia)
        $modules = $modules->filter(function ($module) use ($visibleModuleIds) {
            return in_array($module->id, $visibleModuleIds);
        });

        // Construir árbol de menú
        $menuTree = function ($modules, $parentId = 0) use (&$menuTree, $modulePermissionsMap, $request) {
            $tree = [];
            foreach ($modules as $module) {
                if ((int)$module->parent_id === (int)$parentId) {
                    $item = [
                        'id' => $module->id,
                        'name' => $module->name,
                        'slug' => $module->slug,
                        'icon' => $module->icon,
                        'route' => $module->route,
                        'order' => $module->order,
                        'permissions' => $modulePermissionsMap[$module->id] ?? [],
                        'children' => $menuTree($modules, $module->id)
                    ];
                    // Agregar permisos como submenús si es necesario
                    if ($request->include_permissions) {
                        $permissions = Permission::where('module_id', $module->id)
                            ->where('status', true)
                            ->whereIn('slug', $modulePermissionsMap[$module->id] ?? [])
                            ->orderBy('name')
                            ->get(['id', 'name', 'slug', 'description']);
                        $item['children'] = array_merge($item['children'], $permissions->toArray());
                    }
                    $tree[] = $item;
                }
            }
            return $tree;
        };

        $menu = $menuTree($modules, 0);

        return response()->json([
            'data' => $menu,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
                'permissions' => $user->getAllPermissions()->pluck('slug')
            ]
        ]);
    }

    /**
     * Get user permissions for frontend authorization.
     */
    public function permissions(): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated'
            ], 401);
        }

        $permissions = $user->getAllPermissions()
            ->pluck('slug')
            ->toArray();

        $roles = $user->roles
            ->pluck('name')
            ->toArray();

        return response()->json([
            'permissions' => $permissions,
            'roles' => $roles,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'branch_id' => $user->branch_id,
                'department_id' => $user->department_id,
            ]
        ]);
    }

    /**
     * Check if user has specific permission.
     */
    public function hasPermission(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated'
            ], 401);
        }

        $request->validate([
            'permission' => 'required|string'
        ]);

        $hasPermission = $user->hasPermissionTo($request->permission);

        return response()->json([
            'has_permission' => $hasPermission,
            'permission' => $request->permission
        ]);
    }

    /**
     * Check if user has any of the specified permissions.
     */
    public function hasAnyPermission(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated'
            ], 401);
        }

        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string'
        ]);

        $hasAnyPermission = $user->hasAnyPermission($request->permissions);

        return response()->json([
            'has_any_permission' => $hasAnyPermission,
            'permissions' => $request->permissions
        ]);
    }

    /**
     * Get user's accessible modules.
     */
    public function modules(): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated'
            ], 401);
        }

        // Obtener todos los módulos activos
        $allModules = Module::where('is_active', true)
            ->orderBy('order')
            ->get(['id', 'name', 'slug', 'icon', 'route', 'order', 'parent_id']);

        $userPermissions = $user->getAllPermissions();
        $permittedModuleIds = $userPermissions->pluck('module_id')->unique()->toArray();

        // Identificar todos los módulos visibles (directos + ancestros)
        $visibleModuleIds = [];
        
        $addModuleAndAncestors = function($moduleId) use (&$visibleModuleIds, $allModules, &$addModuleAndAncestors) {
            if (in_array($moduleId, $visibleModuleIds)) return;
            
            $module = $allModules->firstWhere('id', $moduleId);
            if (!$module) return;
            
            $visibleModuleIds[] = $moduleId;
            if ($module->parent_id) {
                $addModuleAndAncestors($module->parent_id);
            }
        };

        foreach ($permittedModuleIds as $moduleId) {
            $addModuleAndAncestors($moduleId);
        }

        $modules = $allModules->filter(function ($module) use ($visibleModuleIds) {
            return in_array($module->id, $visibleModuleIds);
        });

        // Construir árbol de módulos
        $buildTree = function ($modules, $parentId = 0) use (&$buildTree) {
            $tree = [];
            foreach ($modules as $module) {
                if ((int)$module->parent_id === (int)$parentId) {
                    $children = $buildTree($modules, $module->id);
                    $item = $module->toArray();
                    $item['children'] = $children;
                    $tree[] = $item;
                }
            }
            return $tree;
        };

        $modulesTree = $buildTree($modules, 0);

        return response()->json([
            'modules' => $modulesTree,
            'total_permissions' => $userPermissions->count()
        ]);
    }
} 