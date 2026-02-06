<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_name' => $this->company_name,
            'commercial_name' => $this->commercial_name,
            'tax_id' => $this->tax_id,
            'internal_code' => $this->internal_code, // Laravel lo pasa como null, Vue lo convierte a ''
            
            // CORRECCIÓN: Asegurar tipos numéricos para evitar strings vacíos en inputs type="number"
            'lead_time_days' => $this->lead_time_days !== null ? (int) $this->lead_time_days : null,
            'min_order_value' => $this->min_order_value !== null ? (float) $this->min_order_value : null,
            'credit_days' => $this->credit_days !== null ? (int) $this->credit_days : null,
            'credit_limit' => $this->credit_limit !== null ? (float) $this->credit_limit : null,
            
            'contact_name' => $this->contact_name,
            'email_orders' => $this->email_orders,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'is_active' => (bool) $this->is_active,
            'notes' => $this->notes,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
        ];
    }
}