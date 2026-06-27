<?php

declare(strict_types=1);

namespace App\Actions\Admin\Inventory\Transformation;

use App\DTOs\Admin\Inventory\TransformationDataDTO;
use App\Models\Inventory\InventoryTransformation;
use App\Models\Inventory\InventoryLot;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\InventoryBalance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class TransformInventoryAction
{
    public function execute(TransformationDataDTO $dto, string $adminId): InventoryTransformation
    {
        return DB::transaction(function () use ($dto, $adminId) {
            $remainingDebt = $dto->quantityRemoved;
            $totalCostSaliente = 0.00;

            $lots = InventoryLot::where('branch_id', $dto->branchId)
                ->where('sku_id', $dto->sourceInventoryLotId)
                ->where('is_quarantine', false)
                ->where('quantity', '>', 0)
                ->where('deleted_epoch', 0)
                ->orderBy('created_at', 'asc')
                ->lockForUpdate()
                ->get();

            if ($lots->isEmpty()) {
                throw new InvalidArgumentException('DÉFICIT_STOCK: No se identificaron capas de costo disponibles para el SKU origen.');
            }

            $consumedLotsData = [];

            foreach ($lots as $lot) {
                if ($remainingDebt <= 0) {
                    break;
                }

                $qtyToTake = min((float)$lot->quantity, $remainingDebt);
                $lot->quantity = DB::raw("quantity - {$qtyToTake}");
                $lot->saveQuietly();

                $totalCostSaliente += ($qtyToTake * (float)$lot->cost_price);
                $remainingDebt -= $qtyToTake;

                $refLot = InventoryLot::find($lot->id);
                $currentBalanceAfter = (float)$refLot->quantity;

                InventoryMovement::insert([
                    'id' => (string) Str::uuid(),
                    'branch_id' => $dto->branchId,
                    'sku_id' => $lot->sku_id,
                    'inventory_lot_id' => $lot->id,
                    'admin_id' => $adminId,
                    'type' => 'OUT_TRANSFORMATION',
                    'quantity' => $qtyToTake,
                    'balance_after' => $currentBalanceAfter,
                    'reference' => 'Transformación Interna: Egreso de Materia Base',
                    'reason' => 'Consumo algorítmico forzado de capa FIFO',
                    'created_at' => now(),
                ]);

                InventoryBalance::where('branch_id', $dto->branchId)
                    ->where('sku_id', $lot->sku_id)
                    ->decrement('total_physical', $qtyToTake);

                $consumedLotsData[] = ['lot_id' => $lot->id, 'qty' => $qtyToTake];
            }

            if ($remainingDebt > 0) {
                throw new InvalidArgumentException('DÉFICIT_STOCK: Las capas físicas FIFO acumulan un saldo insuficiente para cubrir el total removido.');
            }

            $calculatedCostDestino = $totalCostSaliente / $dto->quantityAdded;

            $branchLotCount = DB::table('inventory_lots')
                ->where('branch_id', $dto->branchId)
                ->whereYear('created_at', 2026)
                ->count();

            $prefix = 'TRSF';
            $sequence = str_pad((string)($branchLotCount + 1), 5, '0', STR_PAD_LEFT);
            $destinationLotCode = "LOT-{$prefix}-2026-{$sequence}";

            $destLot = InventoryLot::create([
                'id' => (string) Str::uuid(),
                'purchase_id' => null,
                'transfer_id' => null,
                'branch_id' => $dto->branchId,
                'sku_id' => $dto->destinationSkuId,
                'lot_code' => $destinationLotCode,
                'quantity' => $dto->quantityAdded,
                'initial_quantity' => $dto->quantityAdded,
                'safety_quantity' => 0.000,
                'initial_safety_quantity' => 0.000,
                'reserved_quantity' => 0.000,
                'cost_price' => $calculatedCostDestino,
                'is_quarantine' => false,
                'expiration_date' => $dto->destinationExpirationDate,
                'deleted_epoch' => 0,
            ]);

            InventoryMovement::insert([
                'id' => (string) Str::uuid(),
                'branch_id' => $dto->branchId,
                'sku_id' => $dto->destinationSkuId,
                'inventory_lot_id' => $destLot->id,
                'admin_id' => $adminId,
                'type' => 'IN_TRANSFORMATION',
                'quantity' => $dto->quantityAdded,
                'balance_after' => $dto->quantityAdded,
                'reference' => 'Transformación Interna: Ingreso de Producto Derivado',
                'reason' => 'Inyección atómica de lote fraccionado',
                'created_at' => now(),
            ]);

            InventoryBalance::updateOrCreate(
                ['branch_id' => $dto->branchId, 'sku_id' => $dto->destinationSkuId],
                ['total_physical' => DB::raw("total_physical + {$dto->quantityAdded}")]
            );

            return InventoryTransformation::create([
                'id' => (string) Str::uuid(),
                'branch_id' => $dto->branchId,
                'admin_id' => $adminId,
                'source_inventory_lot_id' => $consumedLotsData[0]['lot_id'],
                'quantity_removed' => $dto->quantityRemoved,
                'destination_inventory_lot_id' => $destLot->id,
                'quantity_added' => $dto->quantityAdded,
                'notes' => 'Procesamiento de deconstrucción y prorrateo de costos FIFO bajo canal cerrado.',
            ]);
        });
    }
}