<?php

declare(strict_types=1);

namespace App\Actions\Admin\Operations\Provider;

use App\Models\Operations\Provider;
use App\DTOs\Admin\Operations\Provider\ProviderData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class UpsertProvider
{
    public function execute(ProviderData $data): Provider
    {
        $provider = DB::transaction(function () use ($data) {
            if ($data->id) {
                $current = Provider::where('id', $data->id)->lockForUpdate()->firstOrFail();
                if ($current->version !== $data->version) {
                    throw new \Exception("CONCURRENCY_ERROR: El registro fue modificado por otro usuario. Recargue para sincronizar.");
                }
            }
            
            $slug = \Illuminate\Support\Str::slug($data->commercial_name ?? $data->company_name);

            return Provider::updateOrCreate(
                ['id' => $data->id],
                [
                    'company_name'    => $data->company_name,
                    'commercial_name' => $data->commercial_name,
                    'slug'            => $slug,
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
                    // Dejar que Laravel maneje de forma limpia el incremento del campo version en el evento de guardado
                ]
            );
        });

        // Efectos secundarios globales ejecutados fuera de la transacción atómica SQL
        Cache::increment('admin_providers_version');

        return $provider;
    }
}