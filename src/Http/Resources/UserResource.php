<?php

namespace Kaely\AuthPackage\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Kaely\AuthPackage\Http\Resources\PersonResource;

/**
 * @OA\Schema(
 *     schema="UserResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", example="john@example.com"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="roles", type="array", @OA\Items(ref="#/components/schemas/Role")),
 *     @OA\Property(property="person", ref="#/components/schemas/Person")
 * )
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $config = config('auth-package.responses');

        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_active' => $this->is_active,
            // 'created_at' => $this->created_at,
            // 'updated_at' => $this->updated_at,
        ];

        // Incluir roles si está configurado
        if ($config['include_user_roles']) {
            $data['roles'] = $this->whenLoaded('roles', function () {
                return $this->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'slug' => $role->slug,
                        'description' => $role->description,
                        'category' => $role->roleCategory ? [
                            'id' => $role->roleCategory->id,
                            'name' => $role->roleCategory->name,
                            'pms_restaurant_id' => $role->roleCategory->pms_restaurant_id
                        ] : null
                    ];
                });
            });
        }

        // Incluir rol principal
        $data['rol_id'] = $this->whenLoaded('roles') && $this->roles->isNotEmpty()
            ? $this->roles->first()->id
            : null;

        // Restaurant Context
        $data['current_restaurant_id'] = $this->current_restaurant_id ?? null;

        // Incluir información de persona
        $data['person'] = $this->whenLoaded('person', function () {
            return new PersonResource($this->person);
        });

        // Incluir restaurantes si están cargados
        $data['restaurants'] = $this->whenLoaded('restaurants', function () {
            return $this->restaurants->map(function ($restaurant) {
                return [
                    'id' => $restaurant->id,
                    'name' => $restaurant->name,
                    'code' => $restaurant->code ?? null,
                    'address' => $restaurant->address ?? null,
                    'phone' => $restaurant->phone ?? null,
                    'email' => $restaurant->email ?? null,
                ];
            });
        });

        return $data;
    }
}