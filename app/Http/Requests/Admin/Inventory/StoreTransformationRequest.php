<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class StoreTransformationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('super_admin')?->hasRole('super_admin') ?? false;
    }

    public function rules(): array
    {
        $quantityRemoved = $this->input('quantity_removed');

        return [
            'branch_id'               => ['required', 'string', 'uuid', Rule::exists('branches', 'id')],
            'quantity_removed'        => ['required', 'numeric', 'min:0.001'],
            'destination_sku_id'      => ['required', 'string', 'uuid', Rule::exists('skus', 'id')->where('is_active', true)],
            'quantity_added'          => ['required', 'numeric', 'min:0.001'],
            'destination_expiration_date' => ['nullable', 'date', 'after:today'],
            'source_inventory_lot_id' => [
                'required', 
                'string', 
                'uuid', 
                Rule::exists('inventory_lots', 'id')->where('is_quarantine', false)->where('deleted_epoch', 0),
                function ($attribute, $value, $fail) use ($quantityRemoved) {
                    if (is_numeric($quantityRemoved)) {
                        $lot = DB::table('inventory_lots')->where('id', $value)->select('quantity')->first();
                        if (!$lot || (float) $lot->quantity < (float) $quantityRemoved) {
                            $fail('Conflicto logístico: El lote de origen no posee existencias ordinarias suficientes.');
                        }
                    }
                }
            ],
        ];
    }
}