<?php

declare(strict_types=1);

namespace App\Actions\Admin\Operations\Branch;

use App\Models\Operations\Branch;

class ToggleBranchPauseAction
{
    public function execute(Branch $branch): bool
    {
        $newState = !$branch->is_operational_paused;
        
        $branch->update([
            'is_operational_paused' => $newState,
        ]);

        return $newState;
    }
}