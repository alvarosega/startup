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
            'selected_skus'   => ['required', 'array', 'min:1'],
            'selected_skus.*' => ['required', 'uuid', Rule::exists('skus', 'id')],
            'branch_id'       => ['required', 'uuid', Rule::exists('branches', 'id')->where('deleted_epoch', 0)],
            'type'            => ['required', 'string', Rule::in(['regular', 'offer', 'wholesale', 'member', 'liquidation', 'staff'])],
            'list_price'      => ['required', 'numeric', 'min:0.01'],
            'final_price'     => [
                'required', 
                'numeric', 
                'min:0.01', 
                'lte:list_price' // Restricción comercial dura: El precio final de venta no puede superar al de lista
            ],
            'min_quantity'    => ['required', 'integer', 'min:1'],
            'priority'        => ['required', 'integer', 'min:1'],
            'valid_from'      => ['required', 'date'],
            'valid_to'        => ['nullable', 'date', 'after:valid_from'], // Restricción cronológica: Fin posterior al inicio
        ];
    }

    public function messages(): array
    {
        return [
            'final_price.lte'   => 'VIOLACIÓN_DE_MARGEN: El precio final de venta no puede ser superior al precio de lista.',
            'valid_to.after'    => 'INCONSISTENCIA_CRONOLÓGICA: La fecha de vencimiento debe ser posterior a la fecha de inicio.',
            'selected_skus.min' => 'FALTA_DESTINO: Debe seleccionar al menos una variante (SKU) para aplicar la regla masiva.',
        ];
    }
}