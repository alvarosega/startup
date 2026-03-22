<?php

namespace App\Services\Geo;

use App\Models\Branch;
use Illuminate\Support\Facades\Cache;

class BranchCoverageService
{
    /**
     * Identifica la sucursal que cubre un punto GPS.
     * Implementa Caché de 24h para evitar hits recurrentes a MariaDB.
     */
    public function identifyBranch(float $lat, float $lng): ?string
    {
        // Recuperamos los polígonos de caché
        $branches = Cache::remember('active_branch_polygons', 86400, function () {
            return Branch::where('is_active', true)
                ->get(['id', 'coverage_polygon'])
                ->toArray();
        });

        foreach ($branches as $branch) {
            // Se asume que coverage_polygon es un array decodificado de JSON: [[lat, lng], ...]
            if ($this->isInside($lat, $lng, $branch['coverage_polygon'])) {
                return (string) $branch['id'];
            }
        }

        return null; // El punto está en "Zona Fantasma" (Sin Cobertura)
    }

    private function isInside(float $lat, float $lng, ?array $polygon): bool
    {
        if (!$polygon || count($polygon) < 3) return false;

        $inside = false;
        $numPoints = count($polygon);
        $j = $numPoints - 1;

        for ($i = 0; $i < $numPoints; $i++) {
            // Estructura de coordenadas: [0] = Latitude, [1] = Longitude
            $pi = $polygon[$i];
            $pj = $polygon[$j];

            if ((($pi[1] > $lng) != ($pj[1] > $lng)) &&
                ($lat < ($pj[0] - $pi[0]) * ($lng - $pi[1]) / ($pj[1] - $pi[1]) + $pi[0])) {
                $inside = !$inside;
            }
            $j = $i;
        }

        return $inside;
    }
}