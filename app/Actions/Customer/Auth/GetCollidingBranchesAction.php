<?php

declare(strict_types=1);

namespace App\Actions\Customer\Auth;

use App\Architecture\ActionResult;
use App\Services\Customer\Queries\BranchQueryService;

final class GetCollidingBranchesAction
{
    public function __construct(
        private readonly BranchQueryService $branchQueryService
    ) {}

    public function execute(?float $lat, ?float $lng): ActionResult
    {
        if ($lat === null || $lng === null) {
            return ActionResult::success([]);
        }

        return ActionResult::success($this->branchQueryService->getCollidingBranches($lat, $lng));
    }
}