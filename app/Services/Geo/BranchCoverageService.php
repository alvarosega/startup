<?php

declare(strict_types=1);

namespace App\Services\Geo;

use App\Services\Customer\Queries\BranchQueryService;
use App\Exceptions\Customer\Auth\OutOfCoverageException;

final class BranchCoverageService
{
    public function __construct(
        private readonly BranchQueryService $branchQueryService
    ) {}

    /**
     * @throws OutOfCoverageException
     */
    public function identifyBranch(float $lat, float $lng): ?string
    {
        $colliding = $this->branchQueryService->getCollidingBranches($lat, $lng);

        if (empty($colliding)) {
            throw new OutOfCoverageException($lat, $lng);
        }

        return $colliding[0]['id'];
    }
}