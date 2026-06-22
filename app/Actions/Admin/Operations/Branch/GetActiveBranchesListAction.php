<?php

declare(strict_types=1);

namespace App\Actions\Admin\Operations\Branch;

use App\Models\Operations\Branch;
use Illuminate\Database\Eloquent\Collection;

final readonly class GetActiveBranchesListAction
{
    /**
     * Retorna una colección ligera de sucursales activas.
     */
    public function execute(): Collection
    {
        return Branch::where('is_active', true)->where('deleted_epoch', 0)->get(['id', 'name']);
    }
}