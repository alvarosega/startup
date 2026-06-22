<?php

declare(strict_types=1);

namespace App\Actions\Admin\Operations\Branch;

use App\Models\Operations\Branch;
use App\DTOs\Admin\Operations\Branch\BranchData;
use Illuminate\Support\Facades\DB;

class UpdateBranch
{
    public function execute(Branch $branch, BranchData $data): bool
    {
        return DB::transaction(function () use ($branch, $data) {
            return $branch->update([
                'name'                        => $data->name,
                'slug'                        => \Illuminate\Support\Str::slug($data->name),
                'city'                        => $data->city,
                'phone'                       => $data->phone,
                'address'                     => $data->address,
                'latitude'                    => $data->latitude,
                'longitude'                   => $data->longitude,
                'coverage_polygon'            => $data->coverage_polygon,
                'opening_hours'               => $data->opening_hours,
                'is_default'                  => $data->is_default,
                'is_active'                   => $data->is_active,
                'delivery_base_fee'           => $data->delivery_base_fee,
                'delivery_price_per_km'       => $data->delivery_price_per_km,
                'surge_multiplier'            => $data->surge_multiplier,
                'min_order_amount'            => $data->min_order_amount,
                'small_order_fee'             => $data->small_order_fee,
                'base_service_fee_percentage' => $data->base_service_fee_percentage,
            ]);
        });
    }
}