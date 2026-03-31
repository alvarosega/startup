<?php

declare(strict_types=1);

namespace App\Http\Resources\Customer\Featured;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class FeaturedProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'name' => mb_convert_encoding((string) $this->name, 'UTF-8', 'UTF-8'),
            'slug' => (string) $this->slug,
            'image_url' => $this->image_path ? asset('storage/' . $this->image_path) : null,
        ];
    }
}