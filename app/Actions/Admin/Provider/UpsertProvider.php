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
            if ($data->id) {
                $current = Provider::findOrFail($data->id);
                if ($current->version !== $data->version) {
                    throw new \Exception("CONCURRENCY_ERROR: El registro fue modificado por otro usuario. Recargue para sincronizar.");
                }
            }
            
            $provider = Provider::updateOrCreate(
                ['id' => $data->id],
                [
                    'company_name'    => $data->company_name,
                    'commercial_name' => $data->commercial_name,
                    'tax_id'          => $data->tax_id,
                    'internal_code'   => $data->internal_code,
                    'contact_name'    => $data->contact_name,
                    'email_orders'    => $data->email_orders,
                    'phone'           => $data->phone,
                    'address'         => $data->address,
                    'city'            => $data->city,
                    'lead_time_days'  => $data->lead_time_days,
                    'min_order_value' => $data->min_order_value,
                    'credit_days'     => $data->credit_days,
                    'credit_limit'    => $data->credit_limit,
                    'is_active'       => $data->is_active,
                    'notes'           => $data->notes,
                    'version'         => $data->version, // El modelo incrementará esto automáticamente en booted()
                ]
            );

            // REGLA 3.B: Invalidación de caché tras escritura
            Cache::increment('admin_providers_version');
            
            return $provider;
        });
    }
}