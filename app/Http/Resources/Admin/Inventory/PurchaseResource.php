<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'branch_id' => $this->branch_id,
            'provider_id' => $this->provider_id,
            'document_number' => $this->document_number,
            'purchase_date' => $this->purchase_date->format('Y-m-d'),
            'payment_type' => $this->payment_type,
            'status' => $this->status,
            'items' => $this->purchaseItems->map(fn($item) => [
                'sku_id' => $item->sku_id,
                'quantity' => $item->quantity
            ])
        ];
    }
}