<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('super_admin')?->hasRole('super_admin') ?? false;
    }

    public function rules(): array
    {
        $listPrice = $this->input('list_price');

        return [
            'sku_id'       => ['required', 'string', 'uuid', Rule::exists('skus', 'id')->where('is_active', true)],
            'branch_id'    => ['required', 'string', 'uuid', Rule::exists('branches', 'id')],
            'type'         => ['required', 'string', 'max:50'],
            'list_price'   => ['required', 'numeric', 'min:0.00'],
            'min_quantity' => ['required', 'integer', 'min:1'],
            'priority'     => ['required', 'integer', 'min:1'],
            'valid_from'   => ['required', 'date'],
            'valid_to'     => ['nullable', 'date', 'after:valid_from'],
            'final_price'  => [
                'required', 
                'numeric', 
                'min:0.00',
                function ($attribute, $value, $fail) use ($listPrice) {
                    if (is_numeric($listPrice) && (float) $value > (float) $listPrice) {
                        $fail('Defecto comercial: El precio final de venta no puede exceder la tarifa de precio de lista.');
                    }
                }
            ],
        ];
    }
}