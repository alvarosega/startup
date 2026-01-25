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
            'internal_code' => $this->internal_code,
            'contact_name' => $this->contact_name,
            'email_orders' => $this->email_orders,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'lead_time_days' => (int) $this->lead_time_days,
            'min_order_value' => (float) $this->min_order_value,
            'credit_days' => (int) $this->credit_days,
            'credit_limit' => (float) $this->credit_limit,
            'is_active' => (bool) $this->is_active,
            'notes' => $this->notes,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}