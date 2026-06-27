<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Inventory\Purchase;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CompletePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('super_admin')?->hasRole('super_admin') ?? false;
    }

    public function rules(): array
    {
        $purchase = $this->route('purchase');
        $branchId = $purchase instanceof \App\Models\Inventory\Purchase ? $purchase->branch_id : null;

        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.sku_id' => ['required', 'string', 'uuid', Rule::exists('purchase_items', 'sku_id')->where('purchase_id', $purchase?->id)],
            'items.*.lot_code' => [
                'required',
                'string',
                'max:32',
                function ($attribute, $value, $fail) use ($branchId) {
                    if ($branchId && $value) {
                        $exists = DB::table('inventory_lots')
                            ->where('branch_id', $branchId)
                            ->where('lot_code', $value)
                            ->where('deleted_epoch', 0)
                            ->exists();
                        if ($exists) {
                            $fail("El código de lote manual '{$value}' ya se encuentra activo en la sucursal de destino.");
                        }
                    }
                }
            ],
            'items.*.expiration_date' => ['nullable', 'date', 'after:today'],
        ];
    }
}