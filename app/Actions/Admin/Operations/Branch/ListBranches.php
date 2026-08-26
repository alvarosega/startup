<?php

declare(strict_types=1);

namespace App\Actions\Admin\Operations\Branch;

use App\Models\Operations\Branch;
use Illuminate\Support\Facades\DB;

class ListBranches
{
    public function execute(): array
    {
        $branches = Branch::select([
            'id', 'name', 'slug', 'city', 'phone', 'address',
            'delivery_base_fee', 'delivery_price_per_km', 'surge_multiplier',
            'min_order_amount', 'small_order_fee', 'base_service_fee_percentage',
            'is_default', 'is_active', 'is_operational_paused',
            DB::raw('ST_AsText(location) as location_wkt'),
            DB::raw('ST_AsText(coverage_polygon) as polygon_wkt')
        ])->orderBy('name', 'asc')->get();

        return array_map(function ($branch) {
            $latitude = 0.0;
            $longitude = 0.0;
            if ($branch->location_wkt && preg_match('/POINT\(([-\d\.]+) ([-\d\.]+)\)/i', $branch->location_wkt, $matches)) {
                $longitude = (float) $matches[1];
                $latitude = (float) $matches[2];
            }

            $coveragePolygon = [];
            if ($branch->polygon_wkt && preg_match('/POLYGON\(\((.+)\)\)/i', $branch->polygon_wkt, $polyMatches)) {
                $cleanedPolygonString = str_replace(', ', ',', $polyMatches[1]);
                $coordPairs = explode(',', $cleanedPolygonString);
                foreach ($coordPairs as $pair) {
                    $coords = explode(' ', trim($pair));
                    if (count($coords) === 2) {
                        $coveragePolygon[] = [(float) $coords[0], (float) $coords[1]];
                    }
                }
            }

            return [
                'id'                          => (string) $branch->id,
                'name'                        => (string) $branch->name,
                'slug'                        => (string) $branch->slug,
                'city'                        => (string) $branch->city,
                'phone'                       => $branch->phone ? (string) $branch->phone : null,
                'address'                     => $branch->address ? (string) $branch->address : null,
                'latitude'                    => $latitude,
                'longitude'                   => $longitude,
                'coverage_polygon'            => $coveragePolygon,
                'is_default'                  => (bool) $branch->is_default,
                'is_active'                   => (bool) $branch->is_active,
                'is_operational_paused'       => (bool) $branch->is_operational_paused,
                'delivery_base_fee'           => (float) $branch->delivery_base_fee,
                'delivery_price_per_km'       => (float) $branch->delivery_price_per_km,
                'surge_multiplier'            => (float) $branch->surge_multiplier,
                'min_order_amount'            => (float) $branch->min_order_amount,
                'small_order_fee'             => (float) $branch->small_order_fee,
                'max_active_orders' => $branch->max_active_orders !== null ? (int) $branch->max_active_orders : null,
                'base_service_fee_percentage' => (float) $branch->base_service_fee_percentage,
            ];
        }, $branches->all());
    }
}