<?php

declare(strict_types=1);

namespace App\Actions\Admin\Inventory\Purchase;

use App\Models\Inventory\Purchase;
use Illuminate\Http\Request;

class ListPurchasesAction
{
    public function execute(Request $request): array
    {
        $purchases = Purchase::with(['branch:id,name', 'provider:id,company_name', 'admin:id,first_name,last_name'])
            ->where('deleted_epoch', 0)
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
                'branch_name' => $purchase->branch ? (string) $purchase->branch->name : null,
                'provider_name' => $purchase->provider ? (string) $purchase->provider->company_name : null,
                'admin_name' => $purchase->admin ? "{$purchase->admin->first_name} {$purchase->admin->last_name}" : null,
            ];
        }, $purchases->items());

        return [
            'data' => $mappedItems,
            'next' => $purchases->nextCursor()?->encode(),
            'prev' => $purchases->previousCursor()?->encode(),
            'query' => $request->query(),
        ];
    }
}