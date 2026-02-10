<?php

namespace Kaely\AuthPackage\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Kaely\AuthPackage\Models\RoleCategory;
use Kaely\AuthPackage\Http\Resources\RoleCategoryResource;
use Kaely\AuthPackage\Http\Resources\RoleCategoryCollection;
use Illuminate\Support\Str;

class RoleCategoryController extends Controller
{
    /**
     * Display a listing of role categories.
     */
    public function index(Request $request): JsonResponse
    {
        $roleCategories = RoleCategory::query()
            ->with('roles')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->pms_restaurant_id, function ($query, $restaurantId) {
                // If specific restaurant requested
                $query->forRestaurant($restaurantId);
            }, function ($query) {
                // If NO restaurant requested -> Default to Global
                // NOTE: If you want to show ALL (Global + All Restaurants) for SuperAdmin, logic goes here.
                // For now, default to Global.
                $query->global();
            })
            ->orderBy($request->sort_by ?? 'name', $request->sort_order ?? 'asc')
            ->paginate($request->per_page ?? 15);

        return response()->json(new RoleCategoryCollection($roleCategories));
    }

    /**
     * Store a newly created role category.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:100|unique:role_categories,slug',
            'description' => 'nullable|string|max:500',
            'pms_restaurant_id' => 'nullable|exists:tenant.pms_restaurants,id',
        ]);

        $data = $request->all();

        // Auto-generar slug si no se proporciona
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Agregar usuario que crea
        $data['user_add'] = auth()->id();

        $roleCategory = RoleCategory::create($data);
        $roleCategory->load('roles');

        return response()->json([
            'message' => 'Role category created successfully',
            'data' => new RoleCategoryResource($roleCategory)
        ], 201);
    }

    /**
     * Display the specified role category.
     */
    public function show(RoleCategory $roleCategory): JsonResponse
    {
        $roleCategory->load('roles');
        return response()->json([
            'data' => new RoleCategoryResource($roleCategory)
        ]);
    }

    /**
     * Update the specified role category.
     */
    public function update(Request $request, RoleCategory $roleCategory = null): JsonResponse
    {
        try {
            // Obtener parámetros de la ruta
            $routeParams = $request->route()->parameters();

            // Obtener el ID de la categoría de rol desde múltiples fuentes
            $roleCategoryId = null;

            if ($roleCategory && $roleCategory->id) {
                $roleCategoryId = $roleCategory->id;
            } elseif (isset($routeParams['roleCategory'])) {
                $roleCategoryId = $routeParams['roleCategory'];
            } elseif (isset($routeParams['role_category'])) {
                $roleCategoryId = $routeParams['role_category'];
            } elseif ($request->has('role_category_id')) {
                $roleCategoryId = $request->input('role_category_id');
            } elseif ($request->has('id')) {
                $roleCategoryId = $request->input('id');
            }

            if (!$roleCategoryId) {


                return response()->json([
                    'message' => 'Role Category ID not found',
                    'debug_info' => [
                        'route_params' => $routeParams,
                        'request_data' => $request->all()
                    ]
                ], 400);
            }

            // Si no tenemos el objeto roleCategory o no tiene ID, buscarlo
            if (!$roleCategory || !$roleCategory->id) {
                $roleCategory = RoleCategory::find($roleCategoryId);

                if (!$roleCategory) {

                    return response()->json([
                        'message' => 'Role Category not found'
                    ], 404);
                }
            }


            // Validar los datos con el ID correcto para la regla unique
            $request->validate([
                'name' => 'required|string|max:255',
                //'slug' => 'nullable|string|max:100|unique:role_categories,slug,' . $roleCategory->id,
                'description' => 'nullable|string|max:500',
                'is_active' => 'nullable|boolean',
            ]);

            $data = $request->all();

            // Agregar usuario que edita
            $data['user_edit'] = Auth::id();



            $roleCategory->update($data);
            $roleCategory->load('roles');



            return response()->json([
                'message' => 'Role category updated successfully',
                'data' => new RoleCategoryResource($roleCategory)
            ]);

        } catch (\Exception $e) {


            return response()->json([
                'message' => 'Error updating role category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified role category.
     */
    public function destroy(RoleCategory $roleCategory = null): JsonResponse
    {
        try {
            // Obtener parámetros de la ruta
            $routeParams = request()->route()->parameters();

            // Obtener el ID de la categoría de rol desde múltiples fuentes
            $roleCategoryId = null;

            if ($roleCategory && $roleCategory->id) {
                $roleCategoryId = $roleCategory->id;
            } elseif (isset($routeParams['roleCategory'])) {
                $roleCategoryId = $routeParams['roleCategory'];
            } elseif (isset($routeParams['role_category'])) {
                $roleCategoryId = $routeParams['role_category'];
            } elseif (request()->has('role_category_id')) {
                $roleCategoryId = request()->input('role_category_id');
            } elseif (request()->has('id')) {
                $roleCategoryId = request()->input('id');
            }

            if (!$roleCategoryId) {


                return response()->json([
                    'message' => 'Role Category ID not found',
                    'debug_info' => [
                        'route_params' => $routeParams,
                        'request_data' => request()->all()
                    ]
                ], 400);
            }

            // Si no tenemos el objeto roleCategory o no tiene ID, buscarlo
            if (!$roleCategory || !$roleCategory->id) {
                $roleCategory = RoleCategory::find($roleCategoryId);

                if (!$roleCategory) {


                    return response()->json([
                        'message' => 'Role Category not found'
                    ], 404);
                }
            }



            // Verificar si hay roles asociados
            if ($roleCategory->roles()->count() > 0) {


                return response()->json([
                    'message' => 'Cannot delete role category with associated roles'
                ], 422);
            }

            $deletedRoleCategory = $roleCategory->toArray();

            // Agregar usuario que elimina antes del soft delete
            $roleCategory->update(['user_deleted' => auth()->id()]);



            $roleCategory->delete();



            return response()->json([
                'message' => 'Role category deleted successfully'
            ]);

        } catch (\Exception $e) {


            return response()->json([
                'message' => 'Error deleting role category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all role categories for dropdown.
     */
    public function active(): JsonResponse
    {
        $roleCategories = RoleCategory::query()
            ->when(request('pms_restaurant_id'), function ($query, $restaurantId) {
                $query->forRestaurant($restaurantId);
            }, function ($query) {
                $query->global();
            })
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'description', 'pms_restaurant_id']);

        return response()->json($roleCategories);
    }

    /**
     * Get roles by category.
     */
    public function roles(RoleCategory $roleCategory): JsonResponse
    {
        $roles = $roleCategory->roles()
            ->with('permissions')
            ->orderBy('name')
            ->get();

        return response()->json([
            'category' => [
                'id' => $roleCategory->id,
                'name' => $roleCategory->name,
                'slug' => $roleCategory->slug,
                'description' => $roleCategory->description,
            ],
            'roles' => $roles
        ]);
    }
}