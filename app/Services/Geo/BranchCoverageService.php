<?php

namespace App\Services\Geo;

use App\Models\Branch;

class BranchCoverageService
{
    public function identifyBranch(float $lat, float $lng): ?string
    {
        // Obtenemos solo sucursales activas y sus polígonos
        $branches = Branch::where('is_active', true)->get(['id', 'coverage_polygon']);

        foreach ($branches as $branch) {
            if ($this->isInside($lat, $lng, $branch->coverage_polygon)) {
                return $branch->id;
            }
        }

        return null; // Fuera de cobertura
    }

    private function isInside(float $lat, float $lng, ?array $polygon): bool
    {
        if (!$polygon || count($polygon) < 3) return false;

        $inside = false;
        $numPoints = count($polygon);
        $j = $numPoints - 1;

        for ($i = 0; $i < $numPoints; $i++) {
            if ((($polygon[$i][1] > $lng) != ($polygon[$j][1] > $lng)) &&
                ($lat < ($polygon[$j][0] - $polygon[$i][0]) * ($lng - $polygon[$i][1]) / ($polygon[$j][1] - $polygon[$i][1]) + $polygon[$i][0])) {
                $inside = !$inside;
            }
            $j = $i;
        }

        return $inside;
    }
}