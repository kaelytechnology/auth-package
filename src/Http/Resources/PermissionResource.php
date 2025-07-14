<?php

namespace Kaely\AuthPackage\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
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
            'code' => $this->code,
            'description' => $this->description,
            'module_id' => $this->module_id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'module' => $this->whenLoaded('module', function () {
                return [
                    'id' => $this->module->id,
                    'name' => $this->module->name,
                    'code' => $this->module->code,
                ];
            }),
        ];
    }
} 