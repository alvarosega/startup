<?php

namespace App\Actions\Admin\Branch;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Collection;

class GetActiveBranchesListAction
{
    /**
     * Retorna una colección ligera de sucursales activas.
     */
    public function execute(): Collection
    {
        return Branch::where('is_active', true)->get(['id', 'name']);
    }
}