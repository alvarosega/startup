<?php

namespace App\Http\Resources\Admin\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => (string) $this->id,
            'parent_id'          => (string) $this->parent_id,
            'name'               => mb_convert_encoding($this->name, 'UTF-8'),
            'slug'               => (string) $this->slug,
            'description'        => (string) $this->description, // AÑADIR ESTE
            'external_code'      => (string) $this->external_code,
            'tax_classification' => (string) $this->tax_classification,
            'requires_age_check' => (bool) $this->requires_age_check,
            'is_active'          => (bool) $this->is_active,
            'is_featured'        => (bool) $this->is_featured,
            'sort_order'         => (int) $this->sort_order,
            'image_url'          => $this->image_path ? Storage::disk('public')->url($this->image_path) : null,
            'icon_url'           => $this->icon_path ? Storage::disk('public')->url($this->icon_path) : null,
            'bg_color'           => (string) $this->bg_color,
            'parent'             => new CategoryResource($this->whenLoaded('parent')),
        ];
    }
}