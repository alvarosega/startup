<?php

declare(strict_types=1);

namespace App\Http\Resources\Customer\Bundle;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BundleBannerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => (string) $this->id,
            'name'        => (string) $this->name,
            'image'       => [
                'mobile'  => $this->image_mobile_path ? asset('storage/' . $this->image_mobile_path) : null,
                'desktop' => $this->image_desktop_path ? asset('storage/' . $this->image_desktop_path) : null,
            ],
            // DATA CRÍTICA: El bundle_id al que pertenece este creativo
            'context_bundle_id' => (string) $this->bundle_id,
            'action_type'       => (string) $this->action_type, // ADD_TO_CART o NAVIGATE
        ];
    }
}