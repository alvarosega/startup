<?php

namespace App\Actions\Price;

use App\DTOs\Price\PriceData;
use App\Models\Sku;
use App\Models\Price;
use Illuminate\Support\Facades\DB;

class UpdateBranchPrice
{
    public function execute(PriceData $data): void
    {
        DB::transaction(function () use ($data) {
            // 1. Estrategia: Si insertamos un precio 'regular', 
            // damos de baja el precio 'regular' anterior de esa sucursal.
            // Si es 'offer', damos de baja ofertas anteriores que se solapen (opcional).
            
            // Buscamos si ya existe un precio activo del MISMO TIPO para esa sucursal
            $existingPrice = Price::query()
                ->where('sku_id', $data->skuId)
                ->where('branch_id', $data->branchId)
                ->where('type', $data->type) // Clave: No tocamos tipos diferentes
                ->where('min_quantity', $data->minQuantity) // Diferencia minorista/mayorista
                ->whereNull('valid_to') // Solo los que están abiertos indefinidamente
                ->latest()
                ->first();

            // Si existe y es diferente, lo cerramos (Soft Delete o cerrar fecha)
            if ($existingPrice) {
                // Opción A: Soft Delete (Historial oculto)
                $existingPrice->delete(); 
                
                // Opción B: Cerrar vigencia (Historial visible)
                // $existingPrice->update(['valid_to' => now()]);
            }

            // 2. Crear el nuevo precio
            Price::create($data->toArray());
        });
    }
}