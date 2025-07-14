<?php

namespace Kaely\AuthPackage\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Kaely\AuthPackage\Models\Role;
use Kaely\AuthPackage\Models\Permission;
use Kaely\AuthPackage\Http\Resources\RoleResource;
use Kaely\AuthPackage\Http\Resources\RoleCollection;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index(Request $request): JsonResponse
    {
        $roles = Role::query()
            ->with('permissions')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy($request->sort_by ?? 'name', $request->sort_order ?? 'asc')
            ->paginate($request->per_page ?? 15);

        return response()->json(new RoleCollection($roles));
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:roles,code',
            'description' => 'nullable|string|max:500',
            'status' => 'boolean',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create($request->except('permissions'));

        if ($request->has('permissions')) {
            $role->permissions()->attach($request->permissions);
        }

        $role->load('permissions');

        return response()->json([
            'message' => 'Role created successfully',
            'data' => new RoleResource($role)
        ], 201);
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role): JsonResponse
    {
        $role->load('permissions');
        return response()->json(new RoleResource($role));
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, Role $role): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:roles,code,' . $role->id,
            'description' => 'nullable|string|max:500',
            'status' => 'boolean',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update($request->except('permissions'));

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        $role->load('permissions');

        return response()->json([
            'message' => 'Role updated successfully',
            'data' => new RoleResource($role)
        ]);
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role): JsonResponse
    {
        // Verificar si hay usuarios asociados
        if ($role->users()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete role with associated users'
            ], 422);
        }

        $role->delete();

        return response()->json([
            'message' => 'Role deleted successfully'
        ]);
    }

    /**
     * Get all active roles for dropdown.
     */
    public function active(): JsonResponse
    {
        $roles = Role::where('status', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json($roles);
    }

    /**
     * Assign permissions to role.
     */
    public function assignPermissions(Request $request, Role $role): JsonResponse
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->permissions()->sync($request->permissions);

        $role->load('permissions');

        return response()->json([
            'message' => 'Permissions assigned successfully',
            'data' => new RoleResource($role)
        ]);
    }

    /**
     * Get role permissions.
     */
    public function permissions(Role $role): JsonResponse
    {
        $permissions = $role->permissions()
            ->with('module')
            ->orderBy('name')
            ->get();

        return response()->json([
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'code' => $role->code,
            ],
            'permissions' => $permissions
        ]);
    }
} 