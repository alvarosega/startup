<?php

declare(strict_types=1);

namespace App\Actions\Admin\Operations\Branch;

use App\DTOs\Admin\Operations\Branch\BranchData;
use App\Models\Operations\Branch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateBranch
{
    public function execute(BranchData $data): Branch
    {
        return DB::transaction(function () use ($data) {
            if ($data->isDefault) {
                Branch::where('is_default', true)->update(['is_default' => false]);
            }

            return Branch::create([
                'name'                        => $data->name,
                'slug'                        => Str::slug($data->name),
                'city'                        => $data->city,
                'phone'                       => $data->phone,
                'address'                     => $data->address,
                'location'                    => DB::raw("ST_GeomFromText('{$data->locationWkt}', 4326)"),
                'coverage_polygon'            => $data->coveragePolygonWkt ? DB::raw("ST_GeomFromText('{$data->coveragePolygonWkt}', 4326)") : null,
                'is_default'                  => $data->isDefault,
                'is_active'                   => $data->isActive,
                'is_operational_paused'       => $data->isOperationalPaused,
                'delivery_base_fee'           => $data->deliveryBaseFee,
                'delivery_price_per_km'       => $data->deliveryPricePerKm,
                'surge_multiplier'            => $data->surgeMultiplier,
                'min_order_amount'            => $data->minOrderAmount,
                'small_order_fee'             => $data->smallOrderFee,
                'base_service_fee_percentage' => $data->baseServiceFeePercentage,
                'deleted_epoch'               => 0,
            ]);
        });
    }
}