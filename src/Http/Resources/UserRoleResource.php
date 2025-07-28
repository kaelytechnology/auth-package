<?php

namespace Kaely\AuthPackage\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="UserRoleResource",
 *     type="object",
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="role_id", type="integer", example=2),
 *     @OA\Property(property="user_name", type="string", example="John Doe"),
 *     @OA\Property(property="user_email", type="string", example="john@example.com"),
 *     @OA\Property(property="user_active", type="boolean", example=true),
 *     @OA\Property(property="role_name", type="string", example="Administrator"),
 *     @OA\Property(property="role_slug", type="string", example="admin"),
 *     @OA\Property(property="role_status", type="boolean", example=true),
 *     @OA\Property(property="category_name", type="string", example="System"),
 *     @OA\Property(property="assigned_at", type="string", format="date-time")
 * )
 */
class UserRoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user_id' => $this->user_id,
            'role_id' => $this->role_id,
            'user_name' => $this->user_name,
            'user_email' => $this->user_email,
            'user_active' => (bool) $this->user_active,
            'role_name' => $this->role_name,
            'role_slug' => $this->role_slug,
            'role_status' => (bool) $this->role_status,
            'category_name' => $this->category_name,
            'assigned_at' => $this->created_at,
        ];
    }
}