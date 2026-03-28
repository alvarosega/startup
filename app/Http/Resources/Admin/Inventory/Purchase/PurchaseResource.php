<?php

namespace App\Http\Resources\Admin\Inventory\Purchase;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'branch_id'        => $this->branch_id,
            'document_number'  => $this->document_number,
            'purchase_date'    => $this->purchase_date->format('Y-m-d'),
            'payment_type'     => $this->payment_type,
            'total_amount'     => (float) $this->total_amount,
            'status'           => $this->status,
            'branch_name'      => $this->branch->name ?? 'Sin Sede',
            'provider_name'    => $this->provider->commercial_name ?? 'Sin Proveedor',
            'admin_name'       => $this->admin ? "{$this->admin->first_name} {$this->admin->last_name}" : 'Sistema',
            'is_safety'        => str_starts_with($this->document_number, 'EMG') || str_starts_with($this->document_number, 'RELOT'),
            'items_count'      => (int) $this->inventory_lots_count, // Usar withCount() en el Controller
        ];
    }
}