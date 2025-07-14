<?php

namespace Kaely\AuthPackage\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        // Incluir roles si está configurado
        if ($config['include_user_roles']) {
            $data['roles'] = $this->whenLoaded('roles', function() {
                return $this->roles->map(function($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'slug' => $role->slug,
                        'description' => $role->description
                    ];
                });
            });
        }

        // Incluir rol principal
        $data['rol_id'] = $this->whenLoaded('roles') && $this->roles->isNotEmpty()
            ? $this->roles->first()->id
            : null;

        // Incluir información de persona
        $data['person'] = $this->whenLoaded('person', function () {
            return new PersonResource($this->person);
        });

        // Incluir sucursales si está configurado
        if ($config['include_user_branches']) {
            $data['branches'] = $this->whenLoaded('branches', function () {
                return $this->branches->isNotEmpty()
                    ? new BranchResource($this->branches->first())
                    : null;
            });
        }

        // Incluir departamentos si está configurado
        if ($config['include_user_departments']) {
            $data['departments'] = $this->whenLoaded('departments', function () {
                return $this->departments->isNotEmpty()
                    ? new DepartmentResource($this->departments->first())
                    : null;
            });
        }

        return $data;
    }
} 