<?php

namespace App\Actions\Admin\Inventory\Purchase;

use App\Models\{Purchase, InventoryLot, InventoryMovement};
use App\DTOs\Admin\Inventory\Purchase\RegisterPurchaseDTO;
use Illuminate\Support\Facades\{DB, Log};
use Exception;

class RegisterInventoryEntryAction 
{
    /**
     * Ejecuta el registro atómico de ingresos.
     * Prioridad: Integridad de Balances y No-Duplicidad.
     */
    public function execute(RegisterPurchaseDTO $dto): Purchase 
    {
        return DB::transaction(function () use ($dto) {
            // 1. BLOQUEO DE IDEMPOTENCIA (Pre-emptive)
            // Intentar insertar la llave antes de procesar. Si falla, es un reintento.
            try {
                DB::table('processed_requests')->insert([
                    'idempotency_key' => $dto->idempotency_key,
                    'processed_at'    => now()
                ]);
            } catch (\Exception $e) {
                throw new Exception("SOLICITUD_YA_PROCESADA: La llave de idempotencia ya existe.");
            }

            // 2. PERSISTENCIA DE CABECERA (Authority: Server)
            $purchase = Purchase::create([
                'branch_id'       => $dto->branch_id,
                'provider_id'     => $dto->provider_id,
                'admin_id'        => $dto->admin_id,
                'document_number' => $this->generateDocNumber($dto->is_emergency),
                'purchase_date'   => $dto->purchase_date,
                'payment_type'    => $dto->payment_type,
                'total_amount'    => $dto->total_amount,
                'status'          => 'COMPLETED'
            ]);

            foreach ($dto->items as $item) {
                // 3. REGISTRO DE LOTE
                $lot = InventoryLot::create([
                    'purchase_id'      => $purchase->id,
                    'branch_id'        => $dto->branch_id,
                    'sku_id'           => $item['sku_id'],
                    'lot_code'         => $this->generateLotCode($dto->is_emergency),
                    'quantity'         => $item['quantity'],
                    'initial_quantity' => $item['quantity'],
                    'is_safety_stock'  => $dto->is_emergency,
                    'unit_cost'        => $item['unit_cost'],
                    'expiration_date'  => $item['expiration_date'] ?? null
                ]);

                // 4. KARDEX (Inmutable)
                InventoryMovement::create([
                    'branch_id'        => $dto->branch_id,
                    'sku_id'           => $item['sku_id'],
                    'inventory_lot_id' => $lot->id,
                    'admin_id'         => $dto->admin_id,
                    'type'             => 'ENTRY_PURCHASE',
                    'quantity'         => $item['quantity'],
                    'unit_cost'        => $item['unit_cost'],
                    'reference'        => "Doc: {$purchase->document_number}"
                ]);

                // 5. ACTUALIZACIÓN ATÓMICA DE BALANCE
                $this->updateBalance($dto->branch_id, $item['sku_id'], (int)$item['quantity'], $dto->is_emergency);
            }

            return $purchase;
        });
    }

    private function updateBalance(string $branchId, string $skuId, int $qty, bool $isSafety): void 
    {
        DB::table('inventory_balances')->updateOrInsert(
            ['branch_id' => $branchId, 'sku_id' => $skuId],
            [
                'total_physical' => DB::raw("total_physical + {$qty}"),
                'total_safety'   => $isSafety ? DB::raw("total_safety + {$qty}") : DB::raw("total_safety"),
                'updated_at'     => now()
            ]
        );
    }

    private function generateDocNumber(bool $isEmergency): string 
    {
        $prefix = $isEmergency ? 'EMG' : 'CMP';
        return "{$prefix}-" . now()->format('YmdHis') . "-" . bin2hex(random_bytes(2));
    }

    private function generateLotCode(bool $isEmergency): string 
    {
        $prefix = $isEmergency ? 'RELOT' : 'LOT';
        return "{$prefix}-" . strtoupper(bin2hex(random_bytes(4)));
    }
}