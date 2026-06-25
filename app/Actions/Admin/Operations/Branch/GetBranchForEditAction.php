<?php

declare(strict_types=1);

namespace App\Actions\Admin\Operations\Branch;

use App\Models\Operations\Branch;
use Illuminate\Support\Facades\DB;

class GetBranchForEditAction
{
    /**
     * Extrae de forma aislada los datos espaciales en texto plano (WKT) y los unifica con los atributos de la sucursal.
     */
    public function execute(Branch $branch): array
    {
        $spatialData = Branch::select([
            DB::raw('ST_AsText(location) as location_wkt'),
            DB::raw('ST_AsText(coverage_polygon) as polygon_wkt')
        ])->where('id', $branch->id)->first();

        $latitude = 0.0;
        $longitude = 0.0;
        if ($spatialData && preg_match('/POINT\(([-\d\.]+) ([-\d\.]+)\)/i', (string) $spatialData->location_wkt, $matches)) {
            $longitude = (float) $matches[1];
            $latitude = (float) $matches[2];
        }

        $coveragePolygon = [];
        if ($spatialData && preg_match('/POLYGON\(\((.+)\)\)/i', (string) $spatialData->polygon_wkt, $polyMatches)) {
            $coordPairs = explode(', ', $polyMatches[1]);
            foreach ($coordPairs as $pair) {
                $coords = explode(' ', $pair);
                if (count($coords) === 2) {
                    $coveragePolygon[] = [(float) $coords[0], (float) $coords[1]];
                }
            }
        }

        // Al estar 'location' y 'coverage_polygon' en $hidden, array_merge inyecta de forma segura los datos limpios y legibles
        return array_merge($branch->toArray(), [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'coverage_polygon' => $coveragePolygon
        ]);
    }
}