<?php

declare(strict_types=1);

namespace App\Actions\Customer\Auth;

use App\Architecture\ActionResult;
use App\Models\Operations\Branch;

final class GetCollidingBranchesAction
{
    public function execute(?float $latitude, ?float $longitude): ActionResult
    {
        if (is_null($latitude) || is_null($longitude)) {
            return ActionResult::success([]);
        }

        $branches = Branch::query()
            ->active()
            ->withinCoverage($latitude, $longitude)
            ->get(['id', 'name'])
            ->map(fn (Branch $branch) => [
                'id' => $branch->id,
                'name' => $branch->name,
            ])
            ->toArray();

        return ActionResult::success($branches);
    }
}