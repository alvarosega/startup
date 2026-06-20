<?php
namespace App\Http\Resources\Admin\Provider;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderResource extends JsonResource {
    public function toArray(Request $request): array {
        return [
            'id'              => (string) $this->id,
            'company_name'    => $this->company_name,
            'commercial_name' => $this->commercial_name,
            'tax_id'          => (string) $this->tax_id,
            'internal_code'   => (string) $this->internal_code,
            'contact_name'    => $this->contact_name,
            'email_orders'    => (string) $this->email_orders,
            'phone'           => (string) $this->phone,
            'address'         => $this->address,
            'city'            => $this->city,
            'lead_time_days'  => (int) $this->lead_time_days,
            'is_active'       => (bool) $this->is_active,
            'version'         => (int) $this->version, // Necesario para el frontend en el Update
            'created_at'      => $this->created_at?->format('Y-m-d H:i:s'),
        ];
        
    }

    private function purify(?string $str): string {
        if (!$str) return '';
        // REGLA 2.C: Blindaje de Strings malformados
        return mb_convert_encoding($str, 'UTF-8', 'UTF-8');
    }
}