<?php

declare(strict_types=1);

namespace App\Actions\Admin\Inventory\Purchase;

use App\DTOs\Admin\Inventory\PurchaseDataDTO;
use App\Models\Inventory\Purchase;
use App\Models\Inventory\PurchaseItem;
use App\Models\Inventory\InventoryLot;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\InventoryBalance;
use App\Models\Operations\Branch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreatePurchaseAction
{
    public function execute(PurchaseDataDTO $dto, string $adminId): Purchase
    {
        return DB::transaction(function () use ($dto, $adminId) {
            $branch = Branch::where('id', $dto->branchId)->lockForUpdate()->firstOrFail();

            $purchase = Purchase::create([
                'id' => (string) Str::uuid(),
                'branch_id' => $dto->branchId,
                'provider_id' => $dto->providerId,
                'admin_id' => $adminId,
                'document_number' => $dto->documentNumber,
                'purchase_date' => $dto->purchaseDate,
                'payment_type' => $dto->paymentType,
                'status' => $dto->status,
                'deleted_epoch' => 0,
            ]);

            foreach ($dto->items as $item) {
                PurchaseItem::create([
                    'id' => (string) Str::uuid(),
                    'purchase_id' => $purchase->id,
                    'sku_id' => $item->skuId,
                    'quantity' => $item->quantity,
                    'cost_price' => $item->costPrice,
                    'deleted_epoch' => 0,
                ]);

                // Si el estado inicial es COMPLETED, se inyectan las capas físicas de forma inmediata
                if ($dto->status === 'COMPLETED') {
                    $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $branch->name), 0, 4));
                    
                    $lotCount = DB::table('inventory_lots')
                        ->where('branch_id', $dto->branchId)
                        ->whereYear('created_at', 2026)
                        ->count();

                    $sequence = str_pad((string)($lotCount + 1), 5, '0', STR_PAD_LEFT);
                    $generatedLotCode = $item->lotCode ?? "LOT-{$prefix}-2026-{$sequence}";

                    $lot = InventoryLot::create([
                        'id' => (string) Str::uuid(),
                        'purchase_id' => $purchase->id,
                        'transfer_id' => null,
                        'branch_id' => $dto->branchId,
                        'sku_id' => $item->skuId,
                        'lot_code' => $generatedLotCode,
                        'quantity' => $item->quantity,
                        'initial_quantity' => $item->quantity,
                        'safety_quantity' => 0.000,
                        'initial_safety_quantity' => 0.000,
                        'reserved_quantity' => 0.000,
                        'cost_price' => $item->costPrice,
                        'is_quarantine' => false,
                        'expiration_date' => $item->expirationDate,
                        'deleted_epoch' => 0,
                    ]);

                    InventoryMovement::insert([
                        'id' => (string) Str::uuid(),
                        'branch_id' => $dto->branchId,
                        'sku_id' => $item->skuId,
                        'inventory_lot_id' => $lot->id,
                        'admin_id' => $adminId,
                        'type' => 'IN_PURCHASE',
                        'quantity' => $item->quantity,
                        'balance_after' => $item->quantity,
                        'reference' => "Compra Interna Directa: {$dto->documentNumber}",
                        'reason' => 'Ingreso de stock por consolidación inmediata de compra.',
                        'created_at' => now(),
                    ]);

                    InventoryBalance::updateOrCreate(
                        ['branch_id' => $dto->branchId, 'sku_id' => $item->skuId],
                        ['total_physical' => DB::raw("total_physical + {$item->quantity}")]
                    );
                }
            }

            return $purchase;
        });
    }
}