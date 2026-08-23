<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Operations\Branch;
use Illuminate\Support\Facades\Auth;
use DomainException;

final class ShopContextService
{
    /**
     * Resuelve el ID de la sucursal activa aplicando jerarquía estricta de base de datos.
     */
    public function getActiveBranchId(): string
    {
        /** @var \App\Models\Users\Customer|null $user */
        $user = Auth::guard('customer')->user();

        if ($user !== null) {
            return $user->branch_id ?? $this->getDefaultBranchId();
        }

        return $this->getDefaultBranchId();
    }

    /**
     * Recupera la sucursal por defecto directo de la base de datos (Sin Caché).
     * Utiliza el local scope operativo formalizado del modelo Branch.
     */
    public function getDefaultBranchId(): string
    {
        /** @var string|null $branchId */
        $branchId = Branch::query()
            ->active()
            ->where('is_default', true)
            ->value('id');

        if (!$branchId) {
            throw new DomainException("Error Crítico: No existe una sucursal activa por defecto configurada en el sistema.");
        }

        return $branchId;
    }

    /**
     * Evalúa si un usuario logueado carece de cobertura logística en su perfil.
     */
    public function isUserOutOfCoverage(): bool
    {
        /** @var \App\Models\Users\Customer|null $user */
        $user = Auth::guard('customer')->user();

        if ($user === null) {
            return false;
        }

        return is_null($user->branch_id);
    }
}