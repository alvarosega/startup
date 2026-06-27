<?php

declare(strict_types=1);

namespace App\Actions\Admin\Inventory\Purchase;

use App\DTOs\Admin\Inventory\Purchase\CompletePurchaseDataDTO;
use App\Models\Inventory\Purchase;
use App\Models\Inventory\InventoryLot;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\InventoryBalance;
use App\Models\Operations\Branch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CompletePendingPurchaseAction
{
    public function execute(Purchase $purchase, CompletePurchaseDataDTO $dto, string $adminId): void
    {
        DB::transaction(function () use ($purchase, $dto, $adminId) {
            // Bloqueo de registro para evitar concurrencia en transiciones de estado simultáneas
            $purchase = Purchase::where('id', $purchase->id)->lockForUpdate()->firstOrFail();

            if ($purchase->status !== 'PENDING') {
                throw new InvalidArgumentException('CONFLICTO_FLUJO: Solo las compras en estado PENDING pueden consolidarse.');
            }

            $branch = Branch::where('id', $purchase->branch_id)->firstOrFail();
            $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $branch->name), 0, 4));

            $purchase->status = 'COMPLETED';
            $purchase->saveQuietly();

            foreach ($purchase->items as $item) {
                if (!isset($dto->items[$item->sku_id])) {
                    throw new InvalidArgumentException("DATO_FALTANTE: Falta la especificación de lote para el SKU asignado.");
                }

                $inputData = $dto->items[$item->sku_id];

                $lotCount = DB::table('inventory_lots')
                    ->where('branch_id', $purchase->branch_id)
                    ->whereYear('created_at', 2026)
                    ->count();

                $sequence = str_pad((string)($lotCount + 1), 5, '0', STR_PAD_LEFT);
                $lotCode = $inputData['lot_code'] ?? "LOT-{$prefix}-2026-{$sequence}";

                $lot = InventoryLot::create([
                    'id' => (string) Str::uuid(),
                    'purchase_id' => $purchase->id,
                    'transfer_id' => null,
                    'branch_id' => $purchase->branch_id,
                    'sku_id' => $item->sku_id,
                    'lot_code' => $lotCode,
                    'quantity' => $item->quantity,
                    'initial_quantity' => $item->quantity,
                    'safety_quantity' => 0.000,
                    'initial_safety_quantity' => 0.000,
                    'reserved_quantity' => 0.000,
                    'cost_price' => $item->cost_price,
                    'is_quarantine' => false,
                    'expiration_date' => $inputData['expiration_date'],
                    'deleted_epoch' => 0,
                ]);

                InventoryMovement::insert([
                    'id' => (string) Str::uuid(),
                    'branch_id' => $purchase->branch_id,
                    'sku_id' => $item->sku_id,
                    'inventory_lot_id' => $lot->id,
                    'admin_id' => $adminId,
                    'type' => 'IN_PURCHASE',
                    'quantity' => $item->quantity,
                    'balance_after' => $item->quantity,
                    'reference' => "Consolidación Compra: {$purchase->document_number}",
                    'reason' => 'Ingreso físico diferido desde estado Pendiente.',
                    'created_at' => now(),
                ]);

                InventoryBalance::updateOrCreate(
                    ['branch_id' => $purchase->branch_id, 'sku_id' => $item->sku_id],
                    ['total_physical' => DB::raw("total_physical + {$item->quantity}")]
                );
            }
        });
    }
}