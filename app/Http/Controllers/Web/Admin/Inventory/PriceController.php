<?php

declare(strict_types=1);

namespace App\Actions\Admin\Inventory\Price;

use App\DTOs\Admin\Inventory\Price\PriceDataDTO;
use App\Models\Inventory\Price;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UpsertPriceAction
{
    /**
     * Ejecuta la validación cronológica y comercial cruzada e inserta la tarifa de forma atómica.
     */
    public function execute(PriceDataDTO $dto, string $adminId): Price
    {
        return DB::transaction(function () use ($dto, $adminId) {
            
            // 1. OBTENER TARIFAS QUE COINCIDAN EN RANGO TEMPORAL (SOLAPAMIENTO CRUDA)
            // Filtro por: misma sucursal, sku, tipo de precio y cantidad mínima operativa
            $overlappingPrices = Price::where('branch_id', $dto->branchId)
                ->where('sku_id', $dto->skuId)
                ->where('type', $dto->type)
                ->where('min_quantity', $dto->minQuantity)
                ->where('deleted_epoch', 0)
                ->where(function ($query) use ($dto) {
                    $query->where(function ($q) use ($dto) {
                        if ($dto->validTo !== null) {
                            $q->where('valid_from', '<=', $dto->validTo);
                        }
                        $q->where(function ($sub) use ($dto) {
                            $sub->whereNull('valid_to')
                                ->orWhere('valid_to', '>=', $dto->validFrom);
                        });
                    });
                })
                ->lockForUpdate()
                ->get();

            foreach ($overlappingPrices as $existing) {
                
                // REGLA DE CONTROL A: Bloqueo por coincidencia exacta de prioridad en la misma ventana temporal
                if ($existing->priority === $dto->priority) {
                    throw ValidationException::withMessages([
                        'valid_from' => "CONFLICTO_CRONOLÓGICO: Ya existe una tarifa con prioridad {$dto->priority} asignada en este mismo intervalo temporal. Exige baja lógica o inicio el segundo posterior."
                    ]);
                }

                // REGLA DE CONTROL B: El de menor prioridad SIEMPRE debe tener un precio mayor al de mayor prioridad
                if ($existing->priority > $dto->priority && $existing->final_price >= $dto->finalPrice) {
                    throw ValidationException::withMessages([
                        'final_price' => "VIOLACIÓN_COMERCIAL: La tarifa propuesta posee una prioridad inferior ({$dto->priority}) a una existente ({$existing->priority}), por ende, su precio final debe ser estrictamente MAYOR a $" . number_format($existing->final_price, 2) . "."
                    ]);
                }

                if ($existing->priority < $dto->priority && $existing->final_price <= $dto->finalPrice) {
                    throw ValidationException::withMessages([
                        'final_price' => "VIOLACIÓN_COMERCIAL: La tarifa propuesta posee una prioridad superior ({$dto->priority}) a una existente ({$existing->priority}), por ende, su precio final debe ser estrictamente MENOR a $" . number_format($existing->final_price, 2) . "."
                    ]);
                }
            }

            // 2. INSERCIÓN LIMPIA E INMUTABLE
            return Price::create([
                'id' => (string) Str::uuid(),
                'sku_id' => $dto->skuId,
                'branch_id' => $dto->branchId,
                'type' => $dto->type,
                'list_price' => $dto->listPrice,
                'final_price' => $dto->finalPrice,
                'min_quantity' => $dto->minQuantity,
                'priority' => $dto->priority,
                'valid_from' => $dto->validFrom,
                'valid_to' => $dto->validTo,
                'created_by_id' => $adminId,
                'updated_by_id' => null,
                'deleted_epoch' => 0,
            ]);
        });
    }
}