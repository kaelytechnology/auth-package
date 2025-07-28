<?php

namespace Kaely\AuthPackage\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Kaely\AuthPackage\Models\Person;
use Kaely\AuthPackage\Models\User;
use Kaely\AuthPackage\Http\Resources\PersonResource;
use Kaely\AuthPackage\Http\Resources\PersonCollection;

class PersonController extends Controller
{
    /**
     * Display a listing of people.
     */
    public function index(Request $request): JsonResponse
    {
        $people = Person::query()
            ->with('user')
            ->when($request->search, function ($query, $search) {
                $query->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%")
                                   ->orWhere('email', 'like', "%{$search}%");
                      });
            })
            ->when($request->gender, function ($query, $gender) {
                $query->where('gender', $gender);
            })
            ->orderBy($request->sort_by ?? 'first_name', $request->sort_order ?? 'asc')
            ->paginate($request->per_page ?? 15);

        return response()->json(new PersonCollection($people));
    }

    /**
     * Store a newly created person.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id|unique:people,user_id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'birth_date' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
        ]);

        $data = $request->all();
        $data['user_add'] = auth()->id();

        $person = Person::create($data);
        $person->load('user');

        return response()->json([
            'message' => 'Person created successfully',
            'data' => new PersonResource($person)
        ], 201);
    }

    /**
     * Display the specified person.
     */
    public function show(Person $person): JsonResponse
    {
        $person->load('user');
        return response()->json([
            'data' => new PersonResource($person)
        ]);
    }

    /**
     * Update the specified person.
     */
    public function update(Request $request, Person $person): JsonResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'birth_date' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
        ]);

        $data = $request->all();
        $data['user_edit'] = auth()->id();

        $person->update($data);
        $person->load('user');

        return response()->json([
            'message' => 'Person updated successfully',
            'data' => new PersonResource($person)
        ]);
    }

    /**
     * Remove the specified person.
     */
    public function destroy(Person $person): JsonResponse
    {
        // Agregar usuario que elimina antes del soft delete
        $person->update(['user_deleted' => auth()->id()]);
        $person->delete();

        return response()->json([
            'message' => 'Person deleted successfully'
        ]);
    }

    /**
     * Get person by user ID.
     */
    public function byUser(User $user): JsonResponse
    {
        $person = $user->person;
        
        if (!$person) {
            return response()->json([
                'message' => 'Person not found for this user'
            ], 404);
        }

        return response()->json([
            'data' => new PersonResource($person)
        ]);
    }

    /**
     * Create or update person for a user.
     */
    public function createOrUpdateForUser(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'birth_date' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
        ]);

        $data = $request->all();
        $data['user_id'] = $user->id;
        
        $person = $user->person;
        
        if ($person) {
            // Actualizar persona existente
            $data['user_edit'] = auth()->id();
            $person->update($data);
            $message = 'Person updated successfully';
        } else {
            // Crear nueva persona
            $data['user_add'] = auth()->id();
            $person = Person::create($data);
            $message = 'Person created successfully';
        }

        $person->load('user');

        return response()->json([
            'message' => $message,
            'data' => new PersonResource($person)
        ]);
    }

    /**
     * Get statistics about people.
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_people' => Person::count(),
            'by_gender' => [
                'male' => Person::where('gender', 'male')->count(),
                'female' => Person::where('gender', 'female')->count(),
                'other' => Person::where('gender', 'other')->count(),
                'not_specified' => Person::whereNull('gender')->count(),
            ],
            'with_phone' => Person::whereNotNull('phone')->count(),
            'with_address' => Person::whereNotNull('address')->count(),
            'with_birth_date' => Person::whereNotNull('birth_date')->count(),
        ];

        return response()->json($stats);
    }
}