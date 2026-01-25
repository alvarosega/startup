<?php

namespace App\Actions\Branch;

use App\DTOs\Branch\BranchData;
use App\Models\Branch;

class UpdateBranch
{
    public function execute(Branch $branch, BranchData $data): Branch
    {
        $branch->update($data->toArray());
        return $branch->fresh();
    }
}