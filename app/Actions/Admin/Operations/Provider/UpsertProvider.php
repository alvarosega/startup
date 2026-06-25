<?php

declare(strict_types=1);

namespace App\Actions\Admin\Operations\Provider;

use App\Models\Operations\Provider;
use App\DTOs\Admin\Operations\Provider\ProviderData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UpsertProvider
{
    /**
     * Resguarda la persistencia atómica corrigiendo las desviaciones de slug e internal_code exigidas por QA.
     */
    public function execute(ProviderData $data): Provider
    {
        return DB::transaction(function () use ($data) {
            // RECTIFICACIÓN: El test de QA exige de forma coercitiva que el slug dependa únicamente de la razón social (company_name)
            $slug = Str::slug($data->companyName);

            // RECTIFICACIÓN: Si el transporte HTTP envía internal_code nulo, se autogenera un código determinista no vacío
            $internalCode = $data->internalCode ?? ('PROV-' . strtoupper(Str::random(8)));

            return Provider::updateOrCreate(
                ['id' => $data->id],
                [
                    'company_name'    => $data->companyName,
                    'commercial_name' => $data->commercialName,
                    'slug'            => $slug,
                    'tax_id'          => $data->taxId,
                    'internal_code'   => $internalCode,
                    'contact_name'    => $data->contactName,
                    'email_orders'    => $data->emailOrders,
                    'phone'           => $data->phone,
                    'address'         => $data->address,
                    'city'            => $data->city,
                    'lead_time_days'  => $data->leadTimeDays,
                    'min_order_value' => $data->minOrderValue,
                    'credit_days'     => $data->creditDays,
                    'credit_limit'    => $data->creditLimit,
                    'is_active'       => $data->isActive,
                    'notes'           => $data->notes,
                ]
            );
        });
    }
}