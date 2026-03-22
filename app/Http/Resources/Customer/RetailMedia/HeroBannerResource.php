<?php

namespace App\Http\Resources\Customer\RetailMedia;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HeroBannerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => (string) $this->id,
            'name'               => (string) mb_convert_encoding($this->name, 'UTF-8', 'UTF-8'),
            'image_mobile_path'  => (string) $this->image_mobile_path,
            'image_desktop_path' => (string) $this->image_desktop_path,
            'target_type'        => (string) $this->target_type,
            'target_id'          => (string) $this->target_id,
            'action_type'        => (string) $this->action_type,
            // Regla 3.C: Desempaquetado seguro para el objeto polimórfico
            'target' => $this->whenLoaded('target', function() {
                return [
                    'slug'        => (string) $this->target->slug,
                    'is_editable' => (bool) ($this->target->is_editable ?? false),
                ];
            }),
        ];
    }
}