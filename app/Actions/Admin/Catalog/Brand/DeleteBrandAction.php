<?php

declare(strict_types=1);

namespace App\Actions\Admin\Catalog\Brand;

use App\Models\Catalog\Brand;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteBrandAction
{
    /**
     * Evalúa las integridades relacionales de llaves foráneas previas a la mutación de borrado lógico.
     */
    public function execute(Brand $brand): bool
    {
        return DB::transaction(function () use ($brand) {
            if ($brand->subBrands()->exists()) {
                throw ValidationException::withMessages([
                    'brand' => 'Operación denegada: Este nodo actúa como raíz de sub-marcas activas.'
                ]);
            }

            if ($brand->products()->exists()) {
                throw ValidationException::withMessages([
                    'brand' => 'Integridad comprometida: Existen productos vinculados a esta marca en la base de datos.'
                ]);
            }

            return (bool) $brand->delete();
        });
    }
}