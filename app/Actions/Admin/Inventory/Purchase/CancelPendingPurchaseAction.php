<?php

declare(strict_types=1);

namespace App\Actions\Admin\Inventory\Purchase;

use App\Models\Inventory\Purchase;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CancelPendingPurchaseAction
{
    public function execute(Purchase $purchase, string $adminId): void
    {
        DB::transaction(function () use ($purchase, $adminId) {
            $purchase = Purchase::where('id', $purchase->id)->lockForUpdate()->firstOrFail();

            if ($purchase->status !== 'PENDING') {
                throw new InvalidArgumentException('CONFLICTO_FLUJO: No se puede cancelar una compra que no esté en estado PENDING.');
            }

            $purchase->status = 'CANCELLED';
            $purchase->saveQuietly();

            // Aplicación estricta de Soft Delete inmediato solicitado para remoción visual
            $purchase->delete();
        });
    }
}