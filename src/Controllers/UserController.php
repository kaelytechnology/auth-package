<?php

namespace Kaely\AuthPackage\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use Kaely\AuthPackage\Models\Role;
use Kaely\AuthPackage\Http\Resources\UserResource;
use Kaely\AuthPackage\Http\Resources\UserCollection;
use Illuminate\Support\Facades\DB;
use Kaely\AuthPackage\Models\Person; 
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): JsonResponse
    {
        $users = User::query()
            ->with(['roles','person'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('is_active', $status);
            })
            ->orderBy($request->sort_by ?? 'name', $request->sort_order ?? 'asc')
            ->paginate($request->per_page ?? 15);

        return response()->json(new UserCollection($users));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'is_active' => 'boolean',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'is_active' => $request->is_active ?? true,
        ]);

        if ($request->has('roles')) {
            $user->roles()->attach($request->roles);
        }

        $user->load(['roles']);

        return response()->json([
            'message' => 'User created successfully',
            'data' => new UserResource($user)
        ], 201);
    }

     /**
     * @OA\Post(
     *     path="/api/v1/auth/register-complete",
     *     summary="Complete user registration",
     *     description="Register a new user with complete profile information, person data and role assignment",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "password_confirmation", "first_name", "last_name"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123"),
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="phone", type="string", example="+1234567890"),
     *             @OA\Property(property="address", type="string", example="123 Main St, City, Country"),
     *             @OA\Property(property="birth_date", type="string", format="date", example="1990-01-01"),
     *             @OA\Property(property="gender", type="string", enum={"male", "female", "other"}, example="male"),
     *             @OA\Property(property="roles", type="array", @OA\Items(type="integer"), example={2, 3}),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User registered successfully with complete profile"),
     *             @OA\Property(property="user", ref="#/components/schemas/User"),
     *             @OA\Property(property="person", type="object"),
     *             @OA\Property(property="roles", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function registerComplete(Request $request): JsonResponse
    {
        $config = config('auth-package.validation');
        
        // Verificar si existe un usuario eliminado con el mismo email
        $deletedUser = User::withTrashed()->where('email', $request->email)->first();
        
        if ($deletedUser && $deletedUser->trashed() && !$request->boolean('force_create')) {
            // Si existe un usuario eliminado y no se fuerza la creación, ofrecer restaurarlo
            \Log::info('Found deleted user with same email', [
                'email' => $request->email,
                'deleted_user_id' => $deletedUser->id,
                'deleted_at' => $deletedUser->deleted_at
            ]);
            
            return response()->json([
                'message' => 'A user with this email was previously deleted. Would you like to restore it?',
                'action' => 'restore_user',
                'deleted_user' => [
                    'id' => $deletedUser->id,
                    'email' => $deletedUser->email,
                    'name' => $deletedUser->name,
                    'deleted_at' => $deletedUser->deleted_at
                ],
                'options' => [
                    'restore' => 'Use restore endpoint to reactivate this user',
                    'force_create' => 'Add force_create=true to create a new user anyway'
                ]
            ], 409); // Conflict status
        }
        
        // Si se fuerza la creación, modificar el email del usuario eliminado para evitar conflictos
        if ($deletedUser && $deletedUser->trashed() && $request->boolean('force_create')) {
            \Log::info('Force creating new user, modifying deleted user email', [
                'original_email' => $deletedUser->email,
                'deleted_user_id' => $deletedUser->id
            ]);
            
            // Cambiar el email del usuario eliminado para evitar conflictos
            $timestamp = now()->timestamp;
            $deletedUser->email = $deletedUser->email . '.deleted.' . $timestamp;
            $deletedUser->save();
        }
        
        // Validación extendida para usuario y persona
        $request->validate([
            // Datos del usuario 
            'email' => 'required|string|email|max:255|unique:users,email,NULL,id,deleted_at,NULL',
            'password' => [
                'required',
                'string',
                'min:' . $config['password_min_length'],
                'confirmed'
            ],
            'is_active' => 'sometimes|boolean',
            'force_create' => 'sometimes|boolean', // Para forzar creación ignorando usuario eliminado
            
            // Datos de la persona
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'birth_date' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            
            // Roles
            'roles' => 'nullable|array',
            'roles.*' => 'integer|exists:roles,id'
        ]);

        try {
            // Iniciar transacción para garantizar consistencia
            \DB::beginTransaction();

            // Crear el usuario
            $user = User::create([
                'name' => $request->first_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_active' => $request->get('is_active', true),
                'user_add' => auth()->id() ?? 1, // Usuario que crea (o 1 si no hay autenticado)
            ]);

            // Crear la persona asociada
            $personData = [
                'user_id' => $user->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'user_add' => auth()->id() ?? 1,
            ];

            // Agregar campos opcionales si están presentes
            if ($request->filled('phone')) {
                $personData['phone'] = $request->phone;
            }
            if ($request->filled('address')) {
                $personData['address'] = $request->address;
            }
            if ($request->filled('birth_date')) {
                $personData['birth_date'] = $request->birth_date;
            }
            if ($request->filled('gender')) {
                $personData['gender'] = $request->gender;
            }

            $person = Person::create($personData);

            // Asignar roles si se proporcionaron
            if ($request->filled('roles') && is_array($request->roles)) {
                $validRoles = Role::whereIn('id', $request->roles)
                    ->where('status', true)
                    ->pluck('id')
                    ->toArray();
                
                if (!empty($validRoles)) {
                    $user->roles()->attach($validRoles);
                }
            } else {
                // Asignar rol por defecto si no se especificaron roles
                $defaultRole = Role::where('slug', config('auth-package.roles.default_role', 'user'))
                    ->where('status', true)
                    ->first();
                
                if ($defaultRole) {
                    $user->roles()->attach($defaultRole->id);
                }
            }

            // Confirmar transacción
            \DB::commit();

            // Cargar relaciones para la respuesta
            $user->load(['person', 'roles.roleCategory']);

            return response()->json([
                'message' => 'User registered successfully with complete profile',
                'user' => new UserResource($user),
                'person' => [
                    'id' => $person->id,
                    'first_name' => $person->first_name,
                    'last_name' => $person->last_name,
                    'full_name' => $person->full_name,
                    'phone' => $person->phone,
                    'address' => $person->address,
                    'birth_date' => $person->birth_date,
                    'gender' => $person->gender,
                    'age' => $person->age,
                ],
                'roles' => $user->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'slug' => $role->slug,
                        'description' => $role->description,
                        'category' => $role->roleCategory ? [
                            'id' => $role->roleCategory->id,
                            'name' => $role->roleCategory->name,
                            'slug' => $role->roleCategory->slug,
                        ] : null,
                    ];
                }),
            ], 201);

        } catch (\Exception $e) {
            // Revertir transacción en caso de error
            \DB::rollback();
            
            return response()->json([
                'message' => 'Error creating user with complete profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        $user->load(['roles']);
        return response()->json(new UserResource($user));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user = null): JsonResponse
    {
        try {
            // Obtener el ID del usuario de múltiples fuentes posibles
            $routeParams = $request->route()->parameters();
            $actualUserId = ($user ? $user->id : null) ?? $routeParams['user'] ?? $request->input('user_id') ?? $request->input('id');
            
            // Log inicial con información de la request
            \Log::info('UserController::update called', [
                'user_object_id' => $user ? $user->id : null,
                'route_params' => $routeParams,
                'actual_user_id' => $actualUserId,
                'request_data' => $request->except(['password']),
                'all_users' => User::select('id', 'email', 'name')->get()->toArray()
            ]);
            
            // Validar que tenemos un ID de usuario válido
            if (!$actualUserId) {
                \Log::error('UserController::update - No user ID found', [
                    'user_object' => $user,
                    'route_params' => $routeParams,
                    'request_inputs' => $request->all()
                ]);
                
                return response()->json([
                    'message' => 'User ID not found',
                    'debug' => [
                        'user_object_id' => $user ? $user->id : null,
                        'route_params' => $routeParams
                    ]
                ], 400);
            }
            
            // Buscar el usuario si no lo tenemos
            if (!$user || !$user->id) {
                $user = User::find($actualUserId);
                if (!$user) {
                    return response()->json([
                        'message' => 'User not found',
                        'user_id' => $actualUserId
                    ], 404);
                }
            }
            
            // Verificar que se envíen datos para actualizar
            if (!$request->hasAny(['name', 'email', 'password', 'is_active', 'roles'])) {
                return response()->json([
                    'message' => 'No data provided for update',
                    'error' => 'At least one field must be provided: name, email, password, is_active, or roles'
                ], 422);
            }

            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
                'password' => 'nullable|string|min:8',
                'is_active' => 'sometimes|boolean',
                'roles' => 'sometimes|array',
                'roles.*' => 'exists:roles,id',
            ]);

            $data = $request->except(['password', 'roles']);
            
            if ($request->filled('password')) {
                $data['password'] = bcrypt($request->password);
            }
            
            $user->update($data);

            if ($request->has('roles')) {
                $user->roles()->sync($request->roles);
            }

            $user->load(['roles']);

            return response()->json([
                'message' => 'User updated successfully',
                'data' => new UserResource($user)
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error updating user', [
                'user_id' => $actualUserId ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['password'])
            ]);
            
            return response()->json([
                'message' => 'Error updating user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified user (soft delete).
     */
    public function destroy($userId = null): JsonResponse
    {
        try {
            // Obtener el ID del usuario de múltiples fuentes posibles
            $routeParams = request()->route()->parameters();
            $actualUserId = $userId ?? $routeParams['user'] ?? request()->input('user_id') ?? request()->input('id');
            
            // Log inicial con información de la request
            \Log::info('UserController::destroy called', [
                'raw_user_id' => $userId,
                'route_params' => $routeParams,
                'actual_user_id' => $actualUserId,
                'request_input' => request()->all(),
                'authenticated_user_id' => auth()->id(),
                'request_method' => request()->method(),
                'request_url' => request()->url()
            ]);

            if (!$actualUserId) {
                \Log::error('No user ID provided', [
                    'all_sources_checked' => [
                        'parameter' => $userId,
                        'route_user' => $routeParams['user'] ?? 'not_found',
                        'input_user_id' => request()->input('user_id'),
                        'input_id' => request()->input('id')
                    ]
                ]);
                
                return response()->json([
                    'message' => 'User ID is required',
                    'error' => 'No user ID provided in request'
                ], 400);
            }

            // Intentar encontrar el usuario manualmente
            $user = User::find($actualUserId);
            
            if (!$user) {
                \Log::error('User not found', [
                    'user_id' => $actualUserId,
                    'user_exists_check' => User::where('id', $actualUserId)->exists(),
                    'total_users_count' => User::count(),
                    'all_users' => User::select('id', 'email', 'name')->get()->toArray()
                ]);
                
                return response()->json([
                    'message' => 'User not found',
                    'user_id' => $actualUserId
                ], 404);
            }

            // Log información del usuario encontrado
            \Log::info('User found for deletion', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_name' => $user->name,
                'is_trashed_before' => $user->trashed()
            ]);

            // Verificar que el usuario no está ya eliminado
            if ($user->trashed()) {
                \Log::warning('User already deleted', ['user_id' => $user->id]);
                return response()->json([
                    'message' => 'User is already deleted',
                    'user_id' => $user->id
                ], 404);
            }

            // Verificar que no sea el usuario autenticado
            if (auth()->check() && auth()->id() === $user->id) {
                \Log::warning('Attempt to delete own account', ['user_id' => $user->id]);
                return response()->json([
                    'message' => 'Cannot delete your own account'
                ], 422);
            }

            // Guardar información antes de eliminar
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ];

            // Intentar eliminación
            \Log::info('Attempting user deletion', $userData);
            
            // Usar DB transaction para asegurar consistencia
            DB::beginTransaction();
            
            try {
                // Realizar soft delete
                $deleteResult = $user->delete();
                
                \Log::info('Delete operation result', [
                    'user_id' => $userData['id'],
                    'delete_result' => $deleteResult,
                    'deleted_at_after' => $user->deleted_at
                ]);

                if (!$deleteResult) {
                    DB::rollBack();
                    \Log::error('Delete operation failed', ['user_id' => $userData['id']]);
                    return response()->json([
                        'message' => 'Failed to delete user - operation returned false',
                        'user_id' => $userData['id']
                    ], 500);
                }

                // Verificar que realmente se eliminó
                $user->refresh();
                if (!$user->trashed()) {
                    DB::rollBack();
                    \Log::error('User not marked as deleted after delete()', [
                        'user_id' => $userData['id'],
                        'deleted_at' => $user->deleted_at
                    ]);
                    return response()->json([
                        'message' => 'User deletion verification failed',
                        'user_id' => $userData['id']
                    ], 500);
                }

                DB::commit();
                
                \Log::info('User successfully deleted', [
                    'user_id' => $userData['id'],
                    'deleted_at' => $user->deleted_at
                ]);

                return response()->json([
                    'message' => 'User deleted successfully',
                    'deleted_user' => [
                        'id' => $userData['id'],
                        'name' => $userData['name'],
                        'email' => $userData['email'],
                        'deleted_at' => $user->deleted_at->toISOString()
                    ],
                    'success' => true
                ]);

            } catch (\Exception $dbException) {
                DB::rollBack();
                throw $dbException;
            }

        } catch (\Exception $e) {
            \Log::error('Exception in UserController::destroy', [
                'user_id' => $user->id ?? 'unknown',
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Error deleting user: ' . $e->getMessage(),
                'error_details' => [
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine()
                ],
                'success' => false
            ], 500);
        }
    }

    /**
     * Restore a soft deleted user.
     */
    public function restore($id): JsonResponse
    {
        try {
            $user = User::withTrashed()->find($id);

            if (!$user) {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }

            if (!$user->trashed()) {
                return response()->json([
                    'message' => 'User is not deleted'
                ], 422);
            }

            $restored = $user->restore();

            if (!$restored) {
                return response()->json([
                    'message' => 'Failed to restore user'
                ], 500);
            }

            return response()->json([
                'message' => 'User restored successfully',
                'user' => new UserResource($user->fresh())
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error restoring user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign roles to user.
     */
    public function assignRoles(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user->roles()->sync($request->roles);

        $user->load(['roles']);

        return response()->json([
            'message' => 'Roles assigned successfully',
            'data' => new UserResource($user)
        ]);
    }

    /**
     * Get user roles.
     */
    public function roles(User $user): JsonResponse
    {
        $roles = $user->roles()
            ->with('permissions')
            ->orderBy('name')
            ->get();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'roles' => $roles
        ]);
    }

    /**
     * Get user permissions.
     */
    public function permissions(User $user): JsonResponse
    {
        $permissions = $user->getAllPermissions()
            ->sortBy('name')
            ->values();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'permissions' => $permissions
        ]);
    }
}