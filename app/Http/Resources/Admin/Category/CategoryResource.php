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
            'name'               => $this->sanitizeUtf8($this->name),
            'slug'               => (string) $this->slug,
            'external_code'      => $this->external_code ? (string) $this->external_code : null,
            'description'        => $this->sanitizeUtf8($this->description),
            'tax_classification' => $this->sanitizeUtf8($this->tax_classification),
            'bg_color'           => $this->bg_color ? (string) $this->bg_color : null,
            'seo_title'          => $this->sanitizeUtf8($this->seo_title),
            'seo_description'    => $this->sanitizeUtf8($this->seo_description),
            'requires_age_check' => (bool) $this->requires_age_check,
            'is_active'          => (bool) $this->is_active,
            'is_featured'        => (bool) $this->is_featured,
            'image_url'          => $this->image_path ? Storage::disk('public')->url($this->image_path) : null,
            'icon_url'           => $this->icon_path ? Storage::disk('public')->url($this->icon_path) : null,
            'created_at'         => $this->created_at?->toIso8601String(),
        ];
    }

    private function sanitizeUtf8(?string $str): ?string
    {
        if (!$str) return null;
        return mb_check_encoding($str, 'UTF-8') ? $str : mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
    }
}