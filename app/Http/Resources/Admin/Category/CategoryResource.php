<?php

namespace App\Http\Resources\Admin\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function resolve($request = null)
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'external_code' => $this->external_code,
            'is_active' => (bool) $this->is_active,
            'is_featured' => (bool) $this->is_featured,
            'image_url' => $this->image_path ? \Storage::disk('public')->url($this->image_path) : null,
            'children' => CategoryResource::collection($this->whenLoaded('children')),
        ];
    }
}