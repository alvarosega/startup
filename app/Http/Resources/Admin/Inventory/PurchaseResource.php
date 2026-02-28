<?php

namespace App\Http\Resources\Admin\Inventory;

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
            'payment_due_date' => $this->payment_due_date?->format('Y-m-d'),
            'total_amount'     => (float) $this->total_amount,
            'notes'            => $this->notes,
            'status'           => $this->status,
            
            'branch_name'      => $this->branch->name ?? 'Sin Sede',
            'provider_name'    => $this->provider->commercial_name ?? 'Sin Proveedor',
            'admin_name'       => $this->admin ? "{$this->admin->first_name} {$this->admin->last_name}" : 'Sistema',
            
            'is_safety'        => str_starts_with($this->document_number, 'EMG') || str_starts_with($this->document_number, 'RELOT'),
            'items_count'      => $this->inventoryLots->count(),
            
            'details' => $this->inventoryLots->map(function($lot) {
                return [
                    'id'                => $lot->id,
                    'lot_code'          => $lot->lot_code,
                    'sku_name'          => ($lot->sku?->product?->name ?? 'Err') . ' - ' . ($lot->sku?->name ?? 'Err'),
                    'sku_code'          => $lot->sku?->code ?? 'S/N',
                    'initial_quantity'  => (int) $lot->initial_quantity,
                    'current_quantity'  => (int) $lot->quantity,
                    'reserved_quantity' => (int) $lot->reserved_quantity,
                    'unit_cost'         => (float) $lot->unit_cost,
                    'is_safety_stock'   => (bool) $lot->is_safety_stock,
                    'expiration_date'   => $lot->expiration_date?->format('Y-m-d'),
                ];
            }),
        ];
    }
}