<?php

namespace Kaely\AuthPackage\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Kaely\AuthPackage\Models\Branch;
use Kaely\AuthPackage\Http\Resources\BranchResource;
use Kaely\AuthPackage\Http\Resources\BranchCollection;

class BranchController extends Controller
{
    /**
     * Display a listing of branches.
     */
    public function index(Request $request): JsonResponse
    {
        $branches = Branch::query()
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy($request->sort_by ?? 'name', $request->sort_order ?? 'asc')
            ->paginate($request->per_page ?? 15);

        return response()->json(new BranchCollection($branches));
    }

    /**
     * Store a newly created branch.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:branches,code',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'boolean',
        ]);

        $branch = Branch::create($request->all());

        return response()->json([
            'message' => 'Branch created successfully',
            'data' => new BranchResource($branch)
        ], 201);
    }

    /**
     * Display the specified branch.
     */
    public function show(Branch $branch): JsonResponse
    {
        return response()->json(new BranchResource($branch));
    }

    /**
     * Update the specified branch.
     */
    public function update(Request $request, Branch $branch): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:branches,code,' . $branch->id,
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'boolean',
        ]);

        $branch->update($request->all());

        return response()->json([
            'message' => 'Branch updated successfully',
            'data' => new BranchResource($branch)
        ]);
    }

    /**
     * Remove the specified branch.
     */
    public function destroy(Branch $branch): JsonResponse
    {
        // Verificar si hay usuarios asociados
        if ($branch->users()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete branch with associated users'
            ], 422);
        }

        $branch->delete();

        return response()->json([
            'message' => 'Branch deleted successfully'
        ]);
    }

    /**
     * Get all active branches for dropdown.
     */
    public function active(): JsonResponse
    {
        $branches = Branch::where('status', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json($branches);
    }
} 