<?php

namespace App\Http\Resources\Customer\Shop;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LandingCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => (string) $this->id,
            'name'      => (string) mb_convert_encoding($this->name, 'UTF-8', 'UTF-8'),
            'slug'      => (string) $this->slug,
            'image_url' => $this->image_path 
                ? asset('storage/' . $this->image_path) 
                : asset('assets/img/placeholder.png'),
            'bg_color'  => (string) ($this->bg_color ?? '#F3F4F6'),
        ];
    }

    private function purify(?string $str): string {
        if (!$str) return '';
        return mb_check_encoding($str, 'UTF-8') ? $str : mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
    }
}