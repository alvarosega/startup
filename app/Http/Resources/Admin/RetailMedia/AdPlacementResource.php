<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin\RetailMedia;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdPlacementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => (string) $this->id,
            'name'      => mb_toUpperCase((string) $this->name),
            'code'      => (string) $this->code,
            'max_items' => (int) $this->max_items,
            'is_active' => (bool) $this->is_active,
        ];
    }
}