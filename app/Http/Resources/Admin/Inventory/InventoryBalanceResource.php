<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryBalanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $available = $this->total_physical - ($this->total_reserved + $this->total_quarantine + $this->total_safety);

        return [
            'branch_id'        => (string) $this->branch_id,
            'sku_id'           => (string) $this->sku_id,
            'sku_name'         => $this->relationLoaded('sku') ? mb_toUpperCase((string) $this->sku->name) : null,
            'sku_code'         => $this->relationLoaded('sku') ? (string) $this->sku->code : null,
            'total_physical'   => (float) $this->total_physical,
            'total_reserved'   => (float) $this->total_reserved,
            'total_safety'     => (float) $this->total_safety,
            'total_quarantine' => (float) $this->total_quarantine,
            'total_available'  => (float) max(0, $available), // Aplicación matemática estricta de la ley del colchón oculto
        ];
    }
}