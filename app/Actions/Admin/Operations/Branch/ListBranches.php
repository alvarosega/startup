<?php

declare(strict_types=1);

namespace App\Actions\Admin\Operations\Branch;

use App\Models\Operations\Branch;
use Illuminate\Database\Eloquent\Collection;

class ListBranches
{
    public function execute(): Collection
    {
        return Branch::orderBy('name', 'asc')->get();
    }
}