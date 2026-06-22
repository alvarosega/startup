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
            'id'              => (string) $this->id,
            'branch_name'     => $this->relationLoaded('branch') ? mb_toUpperCase((string) $this->branch->name) : null,
            'provider_name'   => $this->relationLoaded('provider') ? mb_convert_encoding((string) $this->provider->company_name, 'UTF-8', 'UTF-8') : null,
            'operator_name'   => $this->relationLoaded('admin') ? (string) ($this->admin->first_name . ' ' . $this->admin->last_name) : null,
            'document_number' => (string) $this->document_number,
            'purchase_date'   => $this->purchase_date?->format('Y-m-d'),
            'payment_type'    => (string) $this->payment_type,
            'status'          => (string) $this->status,
            'created_at'      => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}