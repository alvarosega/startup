<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Inventory\Price;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sku_id'        => ['required', 'uuid', Rule::exists('skus', 'id')],
            'branch_id'     => ['required', 'uuid', Rule::exists('branches', 'id')->where('deleted_epoch', 0)],
            'type'          => ['required', 'string', Rule::in(['regular', 'offer', 'wholesale', 'member', 'liquidation', 'staff'])],
            'list_price'    => ['required', 'numeric', 'min:0.01'],
            'final_price'   => [
                'required', 
                'numeric', 
                'min:0.01', 
                'lte:list_price' // Restricción lógica: El precio final jamás puede superar al precio de lista
            ],
            'min_quantity'  => ['required', 'integer', 'min:1'],
            'priority'      => ['required', 'integer', 'min:1'],
            'valid_from'    => ['required', 'date'],
            'valid_to'      => ['nullable', 'date', 'after:valid_from'], // Restricción lógica: El fin de vigencia debe superar al inicio
        ];
    }

    public function messages(): array
    {
        return [
            'final_price.lte' => 'VIOLACIÓN_DE_MARGEN: El precio final no puede ser mayor que el precio de lista de referencia.',
            'valid_to.after'  => 'INCONSISTENCIA_CRONOLÓGICA: La fecha de finalización debe ser posterior a la fecha de inicio de la regla.',
        ];
    }
}