<?php

namespace Kaely\AuthPackage\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Kaely\AuthPackage\Models\Module;
use Kaely\AuthPackage\Http\Resources\ModuleResource;
use Kaely\AuthPackage\Http\Resources\ModuleCollection;

class ModuleController extends Controller
{
    /**
     * Display a listing of modules.
     */
    public function index(Request $request): JsonResponse
    {
        $modules = Module::query()
            ->where('is_active', 1)
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy($request->sort_by ?? 'order', $request->sort_order ?? 'asc')
            ->paginate($request->per_page ?? 200);

        return response()->json(new ModuleCollection($modules));
    }

    /**
     * Store a newly created module.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:50|unique:modules,slug',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:100',
            'route' => 'nullable|string|max:255',
            'order' => 'integer|min:0',
            'status' => 'boolean',
            'parent_id' => 'nullable|integer|exists:modules,id',
        ]);

        $data = $request->all();
        if (!isset($data['parent_id'])) {
            $data['parent_id'] = 0;
        }
        $module = Module::create($data);

        return response()->json([
            'message' => 'Module created successfully',
            'data' => new ModuleResource($module)
        ], 201);
    }

    /**
     * Display the specified module.
     */
    public function show(Module $module): JsonResponse
    {
        return response()->json(new ModuleResource($module));
    }

    /**
     * Update the specified module.
     */
    public function update(Request $request, Module $module): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:50|unique:modules,slug,' . $module->id,
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:100',
            'route' => 'nullable|string|max:255',
            'order' => 'integer|min:0',
            'status' => 'boolean',
            'parent_id' => 'nullable|integer|exists:modules,id',
        ]);

        $data = $request->all();
        if (!isset($data['parent_id'])) {
            $data['parent_id'] = 0;
        }
        $module->update($data);

        return response()->json([
            'message' => 'Module updated successfully',
            'data' => new ModuleResource($module)
        ]);
    }

    /**
     * Remove the specified module.
     */
    public function destroy(Module $module): JsonResponse
    {
        // Verificar si hay roles asociados
        if ($module->roles()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete module with associated roles'
            ], 422);
        }

        $module->delete();

        return response()->json([
            'message' => 'Module deleted successfully'
        ]);
    }

    /**
     * Get all active modules for menu.
     */
    public function active(): JsonResponse
    {
        $modules = Module::where('status', true)
            ->orderBy('order')
            ->get(['id', 'name', 'slug', 'icon', 'route', 'order']);

        return response()->json($modules);
    }

    /**
     * Update module order.
     */
    public function updateOrder(Request $request): JsonResponse
    {
        $request->validate([
            'modules' => 'required|array',
            'modules.*.id' => 'required|exists:modules,id',
            'modules.*.order' => 'required|integer|min:0',
        ]);

        foreach ($request->modules as $moduleData) {
            Module::where('id', $moduleData['id'])
                ->update(['order' => $moduleData['order']]);
        }

        return response()->json([
            'message' => 'Module order updated successfully'
        ]);
    }
}