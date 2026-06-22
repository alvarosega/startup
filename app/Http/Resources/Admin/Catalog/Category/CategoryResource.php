<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin\Catalog\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => (string) $this->id,
            'parent_id'          => $this->parent_id ? (string) $this->parent_id : null,
            'name'               => mb_convert_encoding((string) ($this->name ?? ''), 'UTF-8', 'UTF-8'),
            'slug'               => (string) $this->slug,
            'description'        => $this->description ? mb_convert_encoding((string) $this->description, 'UTF-8', 'UTF-8') : null,
            'external_code'      => $this->external_code,
            'tax_classification' => $this->tax_classification,
            'requires_age_check' => (bool) $this->requires_age_check,
            'is_active'          => (bool) $this->is_active,
            'is_featured'        => (bool) $this->is_featured,
            'sort_order'         => (int) $this->sort_order,
            
            'image_url'          => $this->image_path 
                ? Storage::disk('public')->url($this->image_path) 
                : Storage::disk('public')->url('assets/img/category_placeholder.png'),
            
            'icon_url'           => $this->icon_path 
                ? Storage::disk('public')->url($this->icon_path) 
                : null,

            'bg_color'           => $this->bg_color ? '#' . ltrim((string) $this->bg_color, '#') : '#6366F1',
            
            'parent'             => self::make($this->whenLoaded('parent')),
            'children'           => self::collection($this->whenLoaded('children')),
            'children_count'     => $this->whenCounted('children'),
        ];
    }
}