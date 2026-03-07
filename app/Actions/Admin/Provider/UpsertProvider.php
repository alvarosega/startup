<?php

namespace App\Actions\Admin\Provider;

use App\Models\Provider;
use Illuminate\Support\Facades\DB;

class UpsertProvider {
    public function execute(ProviderData $data): Provider {
        return DB::transaction(function () use ($data) {
            $provider = Provider::updateOrCreate(
                ['id' => $data->id], // Si el ID es null, crea uno nuevo vía HasUuids
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
                ]
            );

            // REGLA 3.B: Purga de Caché tras escritura
            Cache::forget('admin_providers_list');
            return $provider;
        });
    }
}