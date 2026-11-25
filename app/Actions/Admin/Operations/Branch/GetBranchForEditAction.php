


<?php

declare(strict_types=1);

namespace App\Actions\Admin\Operations\Branch;

use App\Models\Operations\Branch;
use Illuminate\Support\Facades\DB;

class GetBranchForEditAction
{
    public function execute(Branch $branch): array
    {
        $spatialData = Branch::select([
            DB::raw('ST_Y(location) as lat'),
            DB::raw('ST_X(location) as lng'),
            DB::raw('ST_AsGeoJSON(coverage_polygon) as polygon_geojson')
        ])->where('id', $branch->id)->first();

        $latitude = $spatialData && $spatialData->lat !== null ? (float) $spatialData->lat : 0.0;
        $longitude = $spatialData && $spatialData->lng !== null ? (float) $spatialData->lng : 0.0;

        $coveragePolygon = [];
        if ($spatialData && $spatialData->polygon_geojson) {
            $geo = json_decode((string) $spatialData->polygon_geojson, true);
            // GeoJSON estándar devuelve estrictamente [[lng, lat], [lng, lat], ...]
            $coveragePolygon = $geo['coordinates'][0] ?? [];
        }

        return array_merge($branch->toArray(), [
            'latitude'         => $latitude,
            'longitude'        => $longitude,
            'coverage_polygon' => $coveragePolygon
        ]);
    }
}