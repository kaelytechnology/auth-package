<?php

namespace Kaely\AuthPackage\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Kaely\AuthPackage\Models\Department;
use Kaely\AuthPackage\Http\Resources\DepartmentResource;
use Kaely\AuthPackage\Http\Resources\DepartmentCollection;

class DepartmentController extends Controller
{
    /**
     * Display a listing of departments.
     */
    public function index(Request $request): JsonResponse
    {
        $departments = Department::query()
            ->with('branch')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
            })
            ->when($request->branch_id, function ($query, $branchId) {
                $query->where('branch_id', $branchId);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy($request->sort_by ?? 'name', $request->sort_order ?? 'asc')
            ->paginate($request->per_page ?? 15);

        return response()->json(new DepartmentCollection($departments));
    }

    /**
     * Store a newly created department.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:departments,code',
            'branch_id' => 'required|exists:branches,id',
            'description' => 'nullable|string|max:500',
            'status' => 'boolean',
        ]);

        $department = Department::create($request->all());

        return response()->json([
            'message' => 'Department created successfully',
            'data' => new DepartmentResource($department)
        ], 201);
    }

    /**
     * Display the specified department.
     */
    public function show(Department $department): JsonResponse
    {
        $department->load('branch');
        return response()->json(new DepartmentResource($department));
    }

    /**
     * Update the specified department.
     */
    public function update(Request $request, Department $department): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:departments,code,' . $department->id,
            'branch_id' => 'required|exists:branches,id',
            'description' => 'nullable|string|max:500',
            'status' => 'boolean',
        ]);

        $department->update($request->all());

        return response()->json([
            'message' => 'Department updated successfully',
            'data' => new DepartmentResource($department)
        ]);
    }

    /**
     * Remove the specified department.
     */
    public function destroy(Department $department): JsonResponse
    {
        // Verificar si hay usuarios asociados
        if ($department->users()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete department with associated users'
            ], 422);
        }

        $department->delete();

        return response()->json([
            'message' => 'Department deleted successfully'
        ]);
    }

    /**
     * Get departments by branch.
     */
    public function byBranch($branchId): JsonResponse
    {
        $departments = Department::where('branch_id', $branchId)
            ->where('status', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json($departments);
    }

    /**
     * Get all active departments for dropdown.
     */
    public function active(): JsonResponse
    {
        $departments = Department::with('branch')
            ->where('status', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'branch_id']);

        return response()->json($departments);
    }
} 