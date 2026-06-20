<?php
namespace App\Actions\Admin\Branch;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Collection;

class ListBranches
{
    /**
     * Recupera sucursales con ordenamiento por ID (UUID v7/v4 friendly).
     * Se puede extender para filtrado por 'city' o 'is_active'.
     */
    public function execute(): Collection
    {
        return Branch::orderBy('name', 'asc')->get();
    }
}