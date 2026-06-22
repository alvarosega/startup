<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin\Operations\Provider;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => (string) $this->id,
            'company_name'    => $this->purify($this->company_name),
            'commercial_name' => $this->purify($this->commercial_name),
            'tax_id'          => (string) $this->tax_id,
            'internal_code'   => (string) $this->internal_code,
            'contact_name'    => $this->purify($this->contact_name),
            'email_orders'    => (string) $this->email_orders,
            'phone'           => (string) $this->phone,
            'address'         => $this->purify($this->address),
            'city'            => (string) $this->city,
            'lead_time_days'  => (int) $this->lead_time_days,
            'min_order_value' => (float) $this->min_order_value,
            'credit_days'     => (int) $this->credit_days,
            'credit_limit'    => (float) $this->credit_limit,
            'is_active'       => (bool) $this->is_active,
            'version'         => (int) $this->version,
            'notes'           => $this->purify($this->notes),
            'created_at'      => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    private function purify(?string $str): ?string
    {
        if ($str === null) {
            return null;
        }
        return mb_convert_encoding($str, 'UTF-8', 'UTF-8');
    }
}