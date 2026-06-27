<?php

declare(strict_types=1);

namespace App\Actions\Admin\Inventory\Removal;

use App\DTOs\Admin\Inventory\Removal\RemovalDataDTO;
use App\Models\Inventory\RemovalRequest;
use App\Models\Inventory\RemovalItem;
use App\Models\Inventory\InventoryLot;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\InventoryBalance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ApplyRemovalAction
{
    /**
     * Procesa la baja directa descontando exclusivamente del stock ordinario disponible.
     */
    public function execute(RemovalDataDTO $dto, string $adminId): RemovalRequest
    {
        return DB::transaction(function () use ($dto, $adminId) {
            
            // 1. Generación del consecutivo oficial inmutable para el año 2026
            $removalCount = DB::table('removal_requests')
                ->whereYear('created_at', 2026)
                ->count();
            
            $sequence = str_pad((string)($removalCount + 1), 5, '0', STR_PAD_LEFT);
            $generatedCode = "REM-2026-{$sequence}";

            $requestModel = RemovalRequest::create([
                'id' => (string) Str::uuid(),
                'code' => $generatedCode,
                'branch_id' => $dto->branchId,
                'admin_id' => $adminId,
                'approved_by_id' => $adminId,
                'status' => 'approved',
                'approved_at' => now(),
                'reason' => $dto->reason,
                'notes' => $dto->notes,
            ]);

            foreach ($dto->items as $index => $item) {
                // Bloqueo de registro del lote bajo alta concurrencia
                $lot = InventoryLot::where('id', $item->inventoryLotId)
                    ->where('branch_id', $dto->branchId)
                    ->where('deleted_epoch', 0)
                    ->lockForUpdate()
                    ->firstOrFail();

                // REGLA DE CONTROL EXTRAORDINARIA: Validación del stock ordinario neto disponible
                if ($lot->quantity < $item->quantity) {
                    throw ValidationException::withMessages([
                        "items.{$index}.quantity" => "STOCK_INSUFICIENTE: El lote '{$lot->lot_code}' cuenta únicamente con {$lot->quantity} unidades ordinarias disponibles. Solicitado: {$item->quantity}."
                    ]);
                }

                // Descuento físico a nivel de lote
                $lot->quantity -= $item->quantity;
                $lot->saveQuietly();

                RemovalItem::create([
                    'id' => (string) Str::uuid(),
                    'removal_request_id' => $requestModel->id,
                    'inventory_lot_id' => $lot->id,
                    'quantity' => $item->quantity,
                    'unit_cost' => $item->unitCost,
                ]);

                // Asiento inmutable en el Kardex de la empresa
                InventoryMovement::insert([
                    'id' => (string) Str::uuid(),
                    'branch_id' => $dto->branchId,
                    'sku_id' => $lot->sku_id,
                    'inventory_lot_id' => $lot->id,
                    'admin_id' => $adminId,
                    'type' => 'OUT_LOSS', // Tipo genérico formalizado
                    'quantity' => $item->quantity,
                    'balance_after' => $lot->quantity,
                    'reference' => "Baja Directa: {$generatedCode}",
                    'reason' => "Motivo operacional especificado: {$dto->reason}.",
                    'created_at' => now(),
                ]);

                // Descuento atómico en la caché de saldos consolidados
                DB::table('inventory_balances')
                    ->where('branch_id', $dto->branchId)
                    ->where('sku_id', $lot->sku_id)
                    ->decrement('total_physical', $item->quantity);
            }

            return $requestModel;
        });
    }
}