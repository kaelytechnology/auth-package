<?php

namespace Kaely\AuthPackage\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Kaely\AuthPackage\Http\Resources\RoleResource;

/**
 * @OA\Schema(
 *     schema="RoleCategoryResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Administration"),
 *     @OA\Property(property="slug", type="string", example="administration"),
 *     @OA\Property(property="description", type="string", example="Administrative roles"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="roles", type="array", @OA\Items(ref="#/components/schemas/Role")),
 *     @OA\Property(property="roles_count", type="integer", example=3)
 * )
 */
class RoleCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'roles' => $this->whenLoaded('roles', function () {
                return RoleResource::collection($this->roles);
            }),
            'roles_count' => $this->whenCounted('roles'),
            'user_add' => $this->user_add,
            'user_edit' => $this->user_edit,
            'user_deleted' => $this->user_deleted,
        ];
    }
}