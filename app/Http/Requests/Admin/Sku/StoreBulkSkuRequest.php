<?php
namespace App\Http\Requests\Admin\Sku;

use Illuminate\Foundation\Http\FormRequest;

class StoreBulkSkuRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'skus' => ['required', 'array', 'min:1'],
            'skus.*.name' => ['required', 'string', 'max:255'],
            'skus.*.code' => ['nullable', 'string', 'unique:skus,code'],
            'skus.*.price' => ['required', 'numeric', 'min:0'],
            'skus.*.conversion_factor' => ['required', 'numeric', 'min:1'],
            'skus.*.weight' => ['required', 'numeric', 'min:0'],
            'skus.*.image' => ['nullable', 'image', 'max:1024'],
        ];
    }
}