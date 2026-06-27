<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Inventory\Purchase;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class StorePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('super_admin')?->hasRole('super_admin') ?? false;
    }

    public function rules(): array
    {
        $branchId = $this->input('branch_id');
        $status = $this->input('status');

        return [
            'branch_id' => ['required', 'string', 'uuid', Rule::exists('branches', 'id')->where('is_active', true)],
            'provider_id' => ['required', 'string', 'uuid', Rule::exists('providers', 'id')->where('is_active', true)],
            'document_number' => ['required', 'string', 'alpha_num', 'max:32'],
            'purchase_date' => ['required', 'date', 'before_or_equal:today'],
            'payment_type' => ['required', 'string', Rule::in(['CASH', 'CREDIT'])],
            'status' => ['required', 'string', Rule::in(['PENDING', 'COMPLETED'])],
            
            'items' => ['required', 'array', 'min:1'],
            'items.*.sku_id' => ['required', 'string', 'uuid', Rule::exists('skus', 'id')->where('is_active', true)],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.cost_price' => ['required', 'numeric', 'min:0.00'],
            
            // RECTIFICACIÓN: Bajo la Opción B, estos campos se exigen al crear solo si entra como COMPLETED directo
            'items.*.lot_code' => [
                Rule::requiredIf($status === 'COMPLETED'),
                'nullable',
                'string',
                'max:32',
                function ($attribute, $value, $fail) use ($branchId, $status) {
                    if ($status === 'COMPLETED' && $branchId && $value) {
                        $exists = DB::table('inventory_lots')
                            ->where('branch_id', $branchId)
                            ->where('lot_code', $value)
                            ->where('deleted_epoch', 0)
                            ->exists();
                        if ($exists) {
                            $fail("El código de lote manual '{$value}' ya se encuentra activo en esta sucursal.");
                        }
                    }
                }
            ],
            'items.*.expiration_date' => [
                Rule::requiredIf($status === 'COMPLETED'),
                'nullable',
                'date',
                'after:purchase_date'
            ],
        ];
    }
}
