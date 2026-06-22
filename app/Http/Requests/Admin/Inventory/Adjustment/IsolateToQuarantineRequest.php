<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Inventory\Adjustment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class StoreIsolateToQuarantineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'inventory_lot_id' => ['required', 'uuid', 'exists:inventory_lots,id'],
            'reason'           => ['required', 'string', 'min:10', 'max:255'],
            'quantity'         => [
                'required',
                'numeric',
                'min:0.001',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $lot = DB::table('inventory_lots')
                        ->where('id', $this->inventory_lot_id)
                        ->first(['quantity', 'reserved_quantity']);

                    if (!$lot) {
                        return;
                    }

                    $available = (float) $lot->quantity - (float) $lot->reserved_quantity;

                    if ((float) $value > $available) {
                        $fail("RESTRICCIÓN_STOCK_DISPONIBLE: No se puede aislar una cantidad superior al inventario líquido ordinario del lote.");
                    }
                }
            ],
        ];
    }
}