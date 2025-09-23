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
                      ->orWhere('slug', 'like', "%{$search}%");
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
            'slug' => 'required|string|max:100|unique:roles,slug',
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
    public function update(Request $request, Role $role = null): JsonResponse
    {
        try {
            // Obtener el ID del rol de múltiples fuentes posibles
            $routeParams = $request->route()->parameters();
            $actualRoleId = ($role ? $role->id : null) ?? $routeParams['role'] ?? $request->input('role_id') ?? $request->input('id');
            
            // Log inicial con información de la request
            \Log::info('RoleController::update called', [
                'role_object_id' => $role ? $role->id : null,
                'route_params' => $routeParams,
                'actual_role_id' => $actualRoleId,
                'request_data' => $request->all(),
                'all_roles' => Role::select('id', 'name', 'slug')->get()->toArray()
            ]);
            
            // Validar que tenemos un ID de rol válido
            if (!$actualRoleId) {
                \Log::error('RoleController::update - No role ID found', [
                    'role_object' => $role,
                    'route_params' => $routeParams,
                    'request_inputs' => $request->all()
                ]);
                
                return response()->json([
                    'message' => 'Role ID not found',
                    'debug' => [
                        'role_object_id' => $role ? $role->id : null,
                        'route_params' => $routeParams
                    ]
                ], 400);
            }
            
            // Buscar el rol si no lo tenemos
            if (!$role || !$role->id) {
                $role = Role::find($actualRoleId);
                if (!$role) {
                    return response()->json([
                        'message' => 'Role not found',
                        'role_id' => $actualRoleId
                    ], 404);
                }
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'slug' => 'required|string|max:100|unique:roles,slug,' . $role->id,
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
            
        } catch (\Exception $e) {
            \Log::error('Error updating role', [
                'role_id' => $actualRoleId ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'message' => 'Error updating role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role = null): JsonResponse
    {
        try {
            // Obtener parámetros de la ruta
            $routeParams = request()->route()->parameters(); 

            // Obtener el ID del rol desde múltiples fuentes
            $roleId = null;
            
            if ($role && $role->id) {
                $roleId = $role->id;
            } elseif (isset($routeParams['role'])) {
                $roleId = $routeParams['role'];
            } elseif (request()->has('role_id')) {
                $roleId = request()->input('role_id');
            } elseif (request()->has('id')) {
                $roleId = request()->input('id');
            }

            if (!$roleId) { 
                return response()->json([
                    'message' => 'Role ID not found',
                    'debug_info' => [
                        'route_params' => $routeParams,
                        'request_data' => request()->all()
                    ]
                ], 400);
            }

            // Si no tenemos el objeto role o no tiene ID, buscarlo
            if (!$role || !$role->id) {
                $role = Role::find($roleId);
                
                if (!$role) { 
                    
                    return response()->json([
                        'message' => 'Role not found'
                    ], 404);
                }
            }
 

            // Verificar si hay usuarios asociados
            if ($role->users()->count() > 0) { 
                
                return response()->json([
                    'message' => 'Cannot delete role with associated users'
                ], 422);
            }

            $deletedRole = $role->toArray();
            $role->delete(); 
            

            return response()->json([
                'message' => 'Role deleted successfully'
            ]);

        } catch (\Exception $e) { 

            return response()->json([
                'message' => 'Error deleting role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all active roles for dropdown.
     */
    public function active(): JsonResponse
    {
        $roles = Role::where('status', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

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
                'slug' => $role->slug,
            ],
            'permissions' => $permissions
        ]);
    }
}