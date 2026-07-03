<?php

declare(strict_types=1);

namespace App\Actions\Customer\Auth;

use App\Architecture\ActionResult;
use App\Services\Customer\Queries\BranchQueryService;

final class GetActiveBranchesAction
{
    public function __construct(
        private readonly BranchQueryService $branchQueryService
    ) {}

    public function execute(): ActionResult
    {
        return ActionResult::success($this->branchQueryService->getActiveBranches());
    }
}