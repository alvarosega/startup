<?php

declare(strict_types=1);

namespace App\Actions\Admin\Inventory\Purchase;

use App\Models\Inventory\Purchase;
use Illuminate\Http\Request;

class ListPurchasesAction
{
    public function execute(Request $request): array
    {
        $search = $request->query('search') ? trim((string) $request->query('search')) : null;

        $purchases = Purchase::with(['branch:id,name', 'provider:id,company_name', 'admin:id,first_name,last_name', 'items.sku.product'])
            ->withTrashed() // Muestra registros con Soft Delete para auditoría de cancelados
            ->when($search, function ($query, $term) {
                $query->where(function ($q) use ($term) {
                    $q->where('document_number', 'like', "%{$term}%")
                      ->orWhereHas('provider', function ($pq) use ($term) {
                          $pq->where('company_name', 'like', "%{$term}%")
                             ->orWhere('commercial_name', 'like', "%{$term}%");
                      })
                      ->orWhereHas('branch', function ($bq) use ($term) {
                          $bq->where('name', 'like', "%{$term}%");
                      })
                      ->orWhereHas('items.sku', function ($sq) use ($term) {
                          $sq->where('code', 'like', "%{$term}%")
                             ->orWhereHas('product', function ($pq) use ($term) {
                                 $pq->where('name', 'like', "%{$term}%");
                             });
                      });
                });
            })
            ->orderBy('id', 'desc')
            ->cursorPaginate(15)
            ->withQueryString();

        $mappedItems = array_map(function ($purchase) {
            return [
                'id' => (string) $purchase->id,
                'document_number' => (string) $purchase->document_number,
                'purchase_date' => $purchase->purchase_date->toDateString(),
                'payment_type' => (string) $purchase->payment_type,
                'status' => (string) $purchase->status,
                'is_deleted' => $purchase->trashed(),
                'branch_name' => $purchase->branch ? (string) $purchase->branch->name : null,
                'provider_name' => $purchase->provider ? (string) $purchase->provider->company_name : null,
                'admin_name' => $purchase->admin ? "{$purchase->admin->first_name} {$purchase->admin->last_name}" : null,
                'items' => $purchase->items->map(fn($item) => [
                    'sku_id' => (string) $item->sku_id,
                    'sku_code' => $item->sku ? (string) $item->sku->code : null,
                    'product_name' => $item->sku && $item->sku->product ? (string) $item->sku->product->name : null,
                    'quantity' => (float) $item->quantity,
                    'cost_price' => (float) $item->cost_price,
                ])->toArray(),
            ];
        }, $purchases->items());

        return [
            'data' => $mappedItems,
            'next' => $purchases->nextCursor()?->encode(),
            'prev' => $purchases->previousCursor()?->encode(),
        ];
    }
}