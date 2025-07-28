<?php

namespace Kaely\AuthPackage\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Kaely\AuthPackage\Models\Permission;
use Kaely\AuthPackage\Http\Resources\PermissionResource;
use Kaely\AuthPackage\Http\Resources\PermissionCollection;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions.
     */
    public function index(Request $request): JsonResponse
    {
        $permissions = Permission::query()
            ->with('module')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%");
            })
            ->when($request->module_id, function ($query, $moduleId) {
                $query->where('module_id', $moduleId);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy($request->sort_by ?? 'name', $request->sort_order ?? 'asc')
            ->paginate($request->per_page ?? 15);

        return response()->json(new PermissionCollection($permissions));
    }

    /**
     * Store a newly created permission.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:permissions,slug',
            'module_id' => 'required|exists:modules,id',
            'description' => 'nullable|string|max:500',
            'status' => 'boolean',
        ]);

        $permission = Permission::create($request->all());

        return response()->json([
            'message' => 'Permission created successfully',
            'data' => new PermissionResource($permission)
        ], 201);
    }

    /**
     * Display the specified permission.
     */
    public function show(Permission $permission): JsonResponse
    {
        $permission->load('module');
        return response()->json(new PermissionResource($permission));
    }

    /**
     * Update the specified permission.
     */
    public function update(Request $request, Permission $permission): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:permissions,slug,' . $permission->id,
            'module_id' => 'required|exists:modules,id',
            'description' => 'nullable|string|max:500',
            'status' => 'boolean',
        ]);

        $permission->update($request->all());

        return response()->json([
            'message' => 'Permission updated successfully',
            'data' => new PermissionResource($permission)
        ]);
    }

    /**
     * Remove the specified permission.
     */
    public function destroy(Permission $permission): JsonResponse
    {
        // Verificar si hay roles asociados
        if ($permission->roles()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete permission with associated roles'
            ], 422);
        }

        $permission->delete();

        return response()->json([
            'message' => 'Permission deleted successfully'
        ]);
    }

    /**
     * Get permissions by module.
     */
    public function byModule($moduleId): JsonResponse
    {
        $permissions = Permission::where('module_id', $moduleId)
            ->where('status', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return response()->json($permissions);
    }

    /**
     * Get all active permissions for dropdown.
     */
    public function active(): JsonResponse
    {
        $permissions = Permission::with('module')
            ->where('status', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'module_id']);

        return response()->json($permissions);
    }

    /**
     * Bulk create permissions for a module.
     */
    public function bulkCreate(Request $request): JsonResponse
    {
        $request->validate([
            'module_id' => 'required|exists:modules,id',
            'permissions' => 'required|array',
            'permissions.*.name' => 'required|string|max:255',
            'permissions.*.slug' => 'required|string|max:100',
            'permissions.*.description' => 'nullable|string|max:500',
        ]);

        $createdPermissions = [];
        foreach ($request->permissions as $permissionData) {
            $permissionData['module_id'] = $request->module_id;
            $permissionData['status'] = true;
            
            $permission = Permission::create($permissionData);
            $createdPermissions[] = new PermissionResource($permission);
        }

        return response()->json([
            'message' => count($createdPermissions) . ' permissions created successfully',
            'data' => $createdPermissions
        ], 201);
    }
} 