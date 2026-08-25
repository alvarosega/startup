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
            $cleaned = str_replace(', ', ',', trim($polyMatches[1]));
            $coordPairs = explode(',', $cleaned);
            foreach ($coordPairs as $pair) {
                $coords = explode(' ', trim($pair));
                if (count($coords) === 2) {
                    $coveragePolygon[] = [(float) $coords[0], (float) $coords[1]];
                }
            }
        }

        return array_merge($branch->toArray(), [
            'latitude'         => $latitude,
            'longitude'        => $longitude,
            'coverage_polygon' => $coveragePolygon
        ]);
    }
}