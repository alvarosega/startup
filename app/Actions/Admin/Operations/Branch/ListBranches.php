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
            'is_default', 'is_active', 'is_operational_paused', 'max_active_orders',
            DB::raw('ST_Latitude(location) as lat'),
            DB::raw('ST_Longitude(location) as lng'),
            DB::raw('ST_AsGeoJSON(coverage_polygon) as polygon_geojson')
        ])
        ->with(['admins' => function ($query) {
            $query->where('is_active', true)->with('roles');
        }])
        ->orderBy('name', 'asc')
        ->get();

        return array_map(function ($branch) {
            $coveragePolygon = [];
            if ($branch->polygon_geojson) {
                $geo = json_decode((string) $branch->polygon_geojson, true);
                $coveragePolygon = $geo['coordinates'][0] ?? [];
            }

            $activeAdmins = $branch->admins ?? collect();
            $managers = $activeAdmins->filter(function ($admin) {
                return $admin->hasRole('branch_manager');
            })->map(function ($admin) {
                return "{$admin->first_name} {$admin->last_name}";
            })->values()->all();

            return [
                'id'                          => (string) $branch->id,
                'name'                        => (string) $branch->name,
                'slug'                        => (string) $branch->slug,
                'city'                        => (string) $branch->city,
                'phone'                       => $branch->phone ? (string) $branch->phone : null,
                'address'                     => $branch->address ? (string) $branch->address : null,
                'latitude'                    => $branch->lat !== null ? (float) $branch->lat : 0.0,
                'longitude'                   => $branch->lng !== null ? (float) $branch->lng : 0.0,
                'coverage_polygon'            => $coveragePolygon,
                'is_default'                  => (bool) $branch->is_default,
                'is_active'                   => (bool) $branch->is_active,
                'is_operational_paused'       => (bool) $branch->is_operational_paused,
                'max_active_orders'           => $branch->max_active_orders !== null ? (int) $branch->max_active_orders : null,
                'delivery_base_fee'           => (float) $branch->delivery_base_fee,
                'delivery_price_per_km'       => (float) $branch->delivery_price_per_km,
                'surge_multiplier'            => (float) $branch->surge_multiplier,
                'min_order_amount'            => (float) $branch->min_order_amount,
                'small_order_fee'             => (float) $branch->small_order_fee,
                'base_service_fee_percentage' => (float) $branch->base_service_fee_percentage,
                'staff_count'                 => $activeAdmins->count(),
                'managers'                    => $managers,
            ];
        }, $branches->all());
    }
}