<?php

namespace Kaely\AuthPackage\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ModuleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code ?? $this->slug,
            'description' => $this->description,
            'icon' => $this->icon,
            'route' => $this->route,
            'order' => $this->order,
            'status' => $this->status ?? $this->is_active,
            'parent_id' => $this->parent_id,
            'children' => ModuleResource::collection($this->whenLoaded('children')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 