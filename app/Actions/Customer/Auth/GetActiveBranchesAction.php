<?php

namespace App\Actions\Customer\Auth;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Collection;

class GetActiveBranchesAction
{
    /**
     * Recupera sucursales activas con los campos necesarios para el mapa de registro.
     */
    public function execute(): Collection
    {
        return Branch::where('is_active', true)
            ->get(['id', 'name', 'latitude', 'longitude', 'coverage_polygon']);
    }
}