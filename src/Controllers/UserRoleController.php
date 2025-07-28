<?php

namespace Kaely\AuthPackage\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Kaely\AuthPackage\Models\User;
use Kaely\AuthPackage\Models\Role;
use Kaely\AuthPackage\Http\Resources\UserResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserRoleController extends Controller
{
    /**
     * Display a listing of user-role assignments.
     */
    public function index(Request $request): JsonResponse
    {
        $query = DB::table('user_role')
            ->join('users', 'user_role.user_id', '=', 'users.id')
            ->join('roles', 'user_role.role_id', '=', 'roles.id')
            ->leftJoin('role_categories', 'roles.role_category_id', '=', 'role_categories.id')
            ->select(
                'user_role.user_id',
                'user_role.role_id',
                'user_role.created_at',
                'users.name as user_name',
                'users.email as user_email',
                'users.is_active as user_active',
                'roles.name as role_name',
                'roles.slug as role_slug',
                'roles.status as role_status',
                'role_categories.name as category_name'
            )
            ->whereNull('users.deleted_at');

        // Filtros
        if ($request->filled('user_search')) {
            $userSearch = $request->user_search;
            $query->where(function ($q) use ($userSearch) {
                $q->where('users.name', 'like', "%{$userSearch}%")
                  ->orWhere('users.email', 'like', "%{$userSearch}%");
            });
        }

        if ($request->filled('role_search')) {
            $roleSearch = $request->role_search;
            $query->where(function ($q) use ($roleSearch) {
                $q->where('roles.name', 'like', "%{$roleSearch}%")
                  ->orWhere('roles.slug', 'like', "%{$roleSearch}%");
            });
        }

        if ($request->filled('user_status')) {
            $query->where('users.is_active', $request->user_status);
        }

        if ($request->filled('role_status')) {
            $query->where('roles.status', $request->role_status);
        }

        if ($request->filled('category_id')) {
            $query->where('roles.role_category_id', $request->category_id);
        }

        // Ordenamiento
        $sortBy = $request->sort_by ?? 'users.name';
        $sortOrder = $request->sort_order ?? 'asc';
        $query->orderBy($sortBy, $sortOrder);

        // Paginación
        $perPage = $request->per_page ?? 15;
        $assignments = $query->paginate($perPage);

        return response()->json([
            'data' => $assignments->items(),
            'pagination' => [
                'current_page' => $assignments->currentPage(),
                'last_page' => $assignments->lastPage(),
                'per_page' => $assignments->perPage(),
                'total' => $assignments->total(),
                'from' => $assignments->firstItem(),
                'to' => $assignments->lastItem(),
            ]
        ]);
    }

    /**
     * Assign a role to a user.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $role = Role::findOrFail($request->role_id);

        // Verificar si la asignación ya existe
        if ($user->roles()->where('role_id', $role->id)->exists()) {
            return response()->json([
                'message' => 'User already has this role assigned'
            ], 422);
        }

        // Asignar el rol
        $user->roles()->attach($role->id, [
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json([
            'message' => 'Role assigned to user successfully',
            'data' => [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'role_id' => $role->id,
                'role_name' => $role->name,
                'assigned_at' => now()->toISOString()
            ]
        ], 201);
    }

    /**
     * Remove a role from a user.
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $role = Role::findOrFail($request->role_id);

        // Verificar si la asignación existe
        if (!$user->roles()->where('role_id', $role->id)->exists()) {
            return response()->json([
                'message' => 'User does not have this role assigned'
            ], 404);
        }

        // Remover el rol
        $user->roles()->detach($role->id);

        return response()->json([
            'message' => 'Role removed from user successfully'
        ]);
    }

    /**
     * Bulk assign roles to multiple users.
     */
    public function bulkAssign(Request $request): JsonResponse
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $users = User::whereIn('id', $request->user_ids)->get();
        $roles = Role::whereIn('id', $request->role_ids)->get();

        $assignedCount = 0;
        $skippedCount = 0;

        foreach ($users as $user) {
            foreach ($roles as $role) {
                if (!$user->roles()->where('role_id', $role->id)->exists()) {
                    $user->roles()->attach($role->id, [
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    $assignedCount++;
                } else {
                    $skippedCount++;
                }
            }
        }

        return response()->json([
            'message' => 'Bulk role assignment completed',
            'data' => [
                'assigned_count' => $assignedCount,
                'skipped_count' => $skippedCount,
                'total_users' => count($request->user_ids),
                'total_roles' => count($request->role_ids)
            ]
        ]);
    }

    /**
     * Bulk remove roles from multiple users.
     */
    public function bulkRemove(Request $request): JsonResponse
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $users = User::whereIn('id', $request->user_ids)->get();
        $removedCount = 0;

        foreach ($users as $user) {
            $removedRoles = $user->roles()->whereIn('role_id', $request->role_ids)->count();
            $user->roles()->detach($request->role_ids);
            $removedCount += $removedRoles;
        }

        return response()->json([
            'message' => 'Bulk role removal completed',
            'data' => [
                'removed_count' => $removedCount,
                'total_users' => count($request->user_ids),
                'total_roles' => count($request->role_ids)
            ]
        ]);
    }

    /**
     * Get users by role.
     */
    public function usersByRole(Role $role): JsonResponse
    {
        $users = $role->users()
            ->with(['roles'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'slug' => $role->slug,
            ],
            'users' => UserResource::collection($users),
            'total_users' => $users->count()
        ]);
    }

    /**
     * Get roles by user.
     */
    public function rolesByUser(User $user): JsonResponse
    {
        $roles = $user->roles()
            ->with(['permissions', 'roleCategory'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'roles' => $roles,
            'total_roles' => $roles->count()
        ]);
    }

    /**
     * Get user-role assignment statistics.
     */
    public function statistics(): JsonResponse
    {
        $totalAssignments = DB::table('user_role')->count();
        
        $userStats = DB::table('user_role')
            ->join('users', 'user_role.user_id', '=', 'users.id')
            ->selectRaw('COUNT(*) as total_users, AVG(role_count) as avg_roles_per_user')
            ->fromSub(
                DB::table('user_role')
                    ->join('users', 'user_role.user_id', '=', 'users.id')
                    ->selectRaw('user_role.user_id, COUNT(*) as role_count')
                    ->whereNull('users.deleted_at')
                    ->groupBy('user_role.user_id'),
                'user_role_counts'
            )
            ->first();

        $roleStats = DB::table('user_role')
            ->join('roles', 'user_role.role_id', '=', 'roles.id')
            ->selectRaw('COUNT(*) as total_roles, AVG(user_count) as avg_users_per_role')
            ->fromSub(
                DB::table('user_role')
                    ->join('roles', 'user_role.role_id', '=', 'roles.id')
                    ->selectRaw('user_role.role_id, COUNT(*) as user_count')
                    ->groupBy('user_role.role_id'),
                'role_user_counts'
            )
            ->first();

        $topRoles = DB::table('user_role')
            ->join('roles', 'user_role.role_id', '=', 'roles.id')
            ->selectRaw('roles.name, roles.slug, COUNT(*) as user_count')
            ->groupBy('roles.id', 'roles.name', 'roles.slug')
            ->orderByDesc('user_count')
            ->limit(5)
            ->get();

        $usersWithoutRoles = User::whereDoesntHave('roles')
            ->whereNull('deleted_at')
            ->count();

        $rolesWithoutUsers = Role::whereDoesntHave('users')
            ->count();

        return response()->json([
            'total_assignments' => $totalAssignments,
            'users_with_roles' => (int) $userStats->total_users,
            'avg_roles_per_user' => round($userStats->avg_roles_per_user ?? 0, 2),
            'roles_with_users' => (int) $roleStats->total_roles,
            'avg_users_per_role' => round($roleStats->avg_users_per_role ?? 0, 2),
            'users_without_roles' => $usersWithoutRoles,
            'roles_without_users' => $rolesWithoutUsers,
            'top_roles' => $topRoles
        ]);
    }

    /**
     * Sync user roles (replace all current roles with new ones).
     */
    public function syncRoles(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $oldRoles = $user->roles()->pluck('roles.id')->toArray();
        $newRoles = $request->role_ids;

        // Sincronizar roles
        $user->roles()->sync($newRoles);

        $user->load(['roles']);

        return response()->json([
            'message' => 'User roles synchronized successfully',
            'data' => [
                'user' => new UserResource($user),
                'changes' => [
                    'added_roles' => array_diff($newRoles, $oldRoles),
                    'removed_roles' => array_diff($oldRoles, $newRoles),
                    'unchanged_roles' => array_intersect($oldRoles, $newRoles)
                ]
            ]
        ]);
    }
}