<?php

declare(strict_types=1);

namespace App\Services\Geo;

use App\Models\Operations\Branch;

final class BranchCoverageService
{
    /**
     * Identifica el UUID de la sucursal que cubre las coordenadas GPS provistas.
     */
    public function identifyBranch(float $lat, float $lng): ?string
    {
        /** @var string|null $branchId */
        $branchId = Branch::query()
            ->active()
            ->withinCoverage($lat, $lng)
            ->value('id');

        return $branchId;
    }
}