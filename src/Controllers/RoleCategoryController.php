<?php

namespace Kaely\AuthPackage\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
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
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
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
    public function update(Request $request, RoleCategory $roleCategory): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:100|unique:role_categories,slug,' . $roleCategory->id,
            'description' => 'nullable|string|max:500',
        ]);

        $data = $request->all();
        
        // Auto-generar slug si no se proporciona
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        
        // Agregar usuario que edita
        $data['user_edit'] = auth()->id();

        $roleCategory->update($data);
        $roleCategory->load('roles');

        return response()->json([
            'message' => 'Role category updated successfully',
            'data' => new RoleCategoryResource($roleCategory)
        ]);
    }

    /**
     * Remove the specified role category.
     */
    public function destroy(RoleCategory $roleCategory): JsonResponse
    {
        // Verificar si hay roles asociados
        if ($roleCategory->roles()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete role category with associated roles'
            ], 422);
        }

        // Agregar usuario que elimina antes del soft delete
        $roleCategory->update(['user_deleted' => auth()->id()]);
        $roleCategory->delete();

        return response()->json([
            'message' => 'Role category deleted successfully'
        ]);
    }

    /**
     * Get all role categories for dropdown.
     */
    public function active(): JsonResponse
    {
        $roleCategories = RoleCategory::orderBy('name')
            ->get(['id', 'name', 'slug', 'description']);

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