<?php

declare(strict_types=1);

namespace App\Http\Resources\Customer\Brand;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BrandBannerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Resolución de la marca desde el anclaje o el target polimórfico
        $brandName = $this->brand?->name ?? $this->target?->name ?? 'Marca Desconocida';
        $brandSlug = $this->brand?->slug ?? $this->target?->slug ?? '';

        return [
            'id'            => (string) $this->id,
            'name'          => (string) $this->name,
            'image_mobile'  => $this->image_mobile_path ? Storage::disk('public')->url($this->image_mobile_path) : null,
            'image_desktop' => $this->image_desktop_path ? Storage::disk('public')->url($this->image_desktop_path) : null,
            'action_type'   => (string) $this->action_type,
            'brand'         => [
                'name' => mb_convert_encoding((string) $brandName, 'UTF-8'),
                'slug' => (string) $brandSlug,
            ]
        ];
    }
}