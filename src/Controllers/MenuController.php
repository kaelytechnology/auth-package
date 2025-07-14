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

        // Obtener módulos activos
        $modules = Module::where('status', true)
            ->orderBy('order')
            ->get();

        $menu = [];

        foreach ($modules as $module) {
            // Verificar si el usuario tiene permisos para este módulo
            $modulePermissions = $user->getAllPermissions()
                ->where('module_id', $module->id)
                ->pluck('code')
                ->toArray();

            if (!empty($modulePermissions)) {
                $menuItem = [
                    'id' => $module->id,
                    'name' => $module->name,
                    'code' => $module->code,
                    'icon' => $module->icon,
                    'route' => $module->route,
                    'order' => $module->order,
                    'permissions' => $modulePermissions,
                    'children' => []
                ];

                // Agregar permisos como submenús si es necesario
                if ($request->include_permissions) {
                    $permissions = Permission::where('module_id', $module->id)
                        ->where('status', true)
                        ->whereIn('code', $modulePermissions)
                        ->orderBy('name')
                        ->get(['id', 'name', 'code', 'description']);

                    $menuItem['children'] = $permissions;
                }

                $menu[] = $menuItem;
            }
        }

        return response()->json([
            'data' => $menu,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
                'permissions' => $user->getAllPermissions()->pluck('code')
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
            ->pluck('code')
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

        $userPermissions = $user->getAllPermissions();
        $moduleIds = $userPermissions->pluck('module_id')->unique();

        $modules = Module::whereIn('id', $moduleIds)
            ->where('status', true)
            ->orderBy('order')
            ->get(['id', 'name', 'code', 'icon', 'route', 'order']);

        return response()->json([
            'modules' => $modules,
            'total_permissions' => $userPermissions->count()
        ]);
    }
} 