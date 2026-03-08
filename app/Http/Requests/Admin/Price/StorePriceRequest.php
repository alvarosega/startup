<?php
namespace App\Http\Requests\Admin\Price;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePriceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'sku_id'       => ['required', 'uuid', Rule::exists('skus', 'id')->whereNull('deleted_at')],
            'branch_id'    => ['required', 'uuid', Rule::exists('branches', 'id')->whereNull('deleted_at')],
            'type'         => ['required', 'string', Rule::in(['regular', 'offer', 'member', 'wholesale', 'liquidation', 'staff'])],
            // Financial rule: final_price cannot be greater than list_price
            'list_price'   => ['required', 'numeric', 'min:0', 'gte:final_price'],
            'final_price'  => ['required', 'numeric', 'min:0'],
            'min_quantity' => ['required', 'integer', 'min:1'],
            'valid_from'   => ['required', 'date'],
            'valid_to'     => ['nullable', 'date', 'after:valid_from'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'list_price.gte' => 'El precio de lista (tachado) debe ser mayor o igual al precio final.',
        ];
    }
}