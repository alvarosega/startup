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
            'sku_id'       => ['required', 'uuid', 'exists:skus,id'],
            'branch_id'    => ['required', 'uuid', 'exists:branches,id'],
            'type'         => ['required', 'string', Rule::in(['regular', 'offer', 'member', 'wholesale', 'liquidation', 'staff'])],
            'list_price'   => ['required', 'numeric', 'min:0'],
            'final_price'  => ['required', 'numeric', 'min:0'],
            'min_quantity' => ['required', 'integer', 'min:1'],
            'priority'     => ['required', 'integer', 'min:0'],
            'valid_from'   => ['required', 'date'],
            'valid_to'     => ['nullable', 'date', 'after_or_equal:valid_from'],
        ];
    }
}