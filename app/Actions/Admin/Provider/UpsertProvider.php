<?php

namespace App\Actions\Admin\Provider;

use App\Models\Provider;
use App\DTOs\Admin\Provider\ProviderData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class UpsertProvider
{
    public function execute(ProviderData $data): Provider
    {
        return DB::transaction(function () use ($data) {
            // REGLA 2.B: Unificación de lógica (Upsert)
            $provider = Provider::updateOrCreate(
                ['id' => $data->id], // Si el ID es null, Eloquent crea uno nuevo (HasUuids)
                [
                    'company_name'    => (string) $data->company_name,
                    'commercial_name' => $data->commercial_name,
                    'tax_id'          => (string) $data->tax_id,
                    'internal_code'   => $data->internal_code,
                    'contact_name'    => $data->contact_name,
                    'email_orders'    => (string) $data->email_orders,
                    'phone'           => $data->phone,
                    'address'         => $data->address,
                    'city'            => $data->city,
                    'lead_time_days'  => (int) $data->lead_time_days,
                    'min_order_value' => (float) $data->min_order_value,
                    'credit_days'     => (int) $data->credit_days,
                    'credit_limit'    => (float) $data->credit_limit,
                    'is_active'       => (bool) $data->is_active,
                    'notes'           => $data->notes,
                ]
            );

            // REGLA 3.B: Invalidación de caché tras escritura
            Cache::increment('admin_providers_version');
            
            return $provider;
        });
    }
}