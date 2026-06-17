<?php

declare(strict_types=1);

namespace App\Actions\Admin\Inventory;

use App\DTOs\Admin\Inventory\PurchaseIntakeDTO;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\InventoryBalance;
use App\Models\InventoryLot;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegisterPurchaseIntakeAction
{
    public function execute(PurchaseIntakeDTO $dto): Purchase
    {
        return DB::transaction(function () use ($dto) {
            $purchase = Purchase::create([
                'branch_id' => $dto->branchId,
                'provider_id' => $dto->providerId,
                'admin_id' => $dto->adminId,
                'document_number' => $dto->documentNumber,
                'purchase_date' => $dto->purchaseDate,
                'payment_type' => $dto->paymentType,
                'status' => 'COMPLETED'
            ]);

            $sortedItems = $dto->items;
            usort($sortedItems, fn($a, $b) => strcmp($a['sku_id'], $b['sku_id']));

            foreach ($sortedItems as $item) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'sku_id' => $item['sku_id'],
                    'quantity' => $item['quantity']
                ]);

                $balance = InventoryBalance::where('branch_id', $dto->branchId)
                    ->where('sku_id', $item['sku_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$balance) {
                    $balance = InventoryBalance::create([
                        'branch_id' => $dto->branchId,
                        'sku_id' => $item['sku_id'],
                        'total_physical' => 0,
                        'total_reserved' => 0,
                        'total_safety' => 0,
                        'total_quarantine' => 0
                    ]);
                }

                $balance->increment('total_physical', $item['quantity']);

                $lot = InventoryLot::where('branch_id', $dto->branchId)
                    ->where('lot_code', $item['lot_code'])
                    ->first();

                if ($lot) {
                    $lot->increment('quantity', $item['quantity']);
                    $lot->increment('initial_quantity', $item['quantity']);
                } else {
                    $lot = InventoryLot::create([
                        'purchase_id' => $purchase->id,
                        'transfer_id' => null,
                        'branch_id' => $dto->branchId,
                        'sku_id' => $item['sku_id'],
                        'lot_code' => $item['lot_code'],
                        'quantity' => $item['quantity'],
                        'initial_quantity' => $item['quantity'],
                        'reserved_quantity' => 0,
                        'is_safety_stock' => false,
                        'expiration_date' => $item['expiration_date']
                    ]);
                }

                InventoryMovement::create([
                    'branch_id' => $dto->branchId,
                    'sku_id' => $item['sku_id'],
                    'inventory_lot_id' => $lot->id,
                    'admin_id' => $dto->adminId,
                    'type' => 'IN_PURCHASE',
                    'quantity' => $item['quantity'],
                    'reference' => 'DOC_' . $dto->documentNumber,
                    'reason' => null,
                    'created_at' => now()
                ]);
            }

            return $purchase;
        });
    }
}