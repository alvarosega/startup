<?php

declare(strict_types=1);

namespace App\Actions\Admin\Inventory\Purchase;

use App\Models\Inventory\Purchase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class GetPurchaseEditDataAction
{
    /**
     * Extrae y deconstruye una compra pendiente validando la inmutabilidad de estados terminales.
     */
    public function execute(Purchase $purchase): array
    {
        // RECTIFICACIÓN: Control riguroso de flujo interrumpiendo la petición si la compra ya fue consolidada o descartada
        if (in_array($purchase->status, ['COMPLETED', 'CANCELLED'], true)) {
            throw new HttpException(403, "VIOLACIÓN_LOGÍSTICA: La compra ya posee el estado terminal '{$purchase->status}' y no admite modificaciones ni ingresos físicos diferidos.");
        }

        $purchase->load([
            'branch:id,name',
            'provider:id,company_name',
            'items.sku.product:id,name'
        ]);

        return [
            'purchase' => [
                'id' => (string) $purchase->id,
                'document_number' => (string) $purchase->document_number,
                'purchase_date' => $purchase->purchase_date->toDateString(),
                'payment_type' => (string) $purchase->payment_type,
                'status' => (string) $purchase->status,
                'branch_name' => $purchase->branch ? (string) $purchase->branch->name : null,
                'provider_name' => $purchase->provider ? (string) $purchase->provider->company_name : null,
                'items' => $purchase->items->map(fn($item) => [
                    'sku_id' => (string) $item->sku_id,
                    'sku_code' => $item->sku ? (string) $item->sku->code : null,
                    'product_name' => $item->sku && $item->sku->product ? (string) $item->sku->product->name : null,
                    'sku_name' => $item->sku ? (string) $item->sku->name : null,
                    'quantity' => (float) $item->quantity,
                    'cost_price' => (float) $item->cost_price,
                ])->toArray(),
            ]
        ];
    }
}