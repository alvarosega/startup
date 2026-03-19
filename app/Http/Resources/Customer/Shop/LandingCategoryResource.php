<?php

namespace App\Http\Resources\Customer\Shop;

use Illuminate\Http\Resources\Json\JsonResource;

class LandingCategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => (string) $this->id,
            'name'       => $this->purify($this->name),
            'slug'       => (string) $this->slug,
            'image_path' => (string) $this->image_path,
            'bg_color'   => (string) ($this->bg_color ?? 'var(--primary)'),
        ];
    }

    private function purify(?string $str): string {
        if (!$str) return '';
        return mb_check_encoding($str, 'UTF-8') ? $str : mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
    }
}