<?php
namespace App\Http\Requests\Admin\Sku;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBulkSkuRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'skus' => ['required', 'array', 'min:1'],
            'skus.*.name' => ['required', 'string', 'max:255'],
            'skus.*.code' => [
                'nullable', 'string', 
                // LA LEY: Ignorar SKUs eliminados
                Rule::unique('skus', 'code')->whereNull('deleted_at')
            ],
            'skus.*.price' => ['required', 'numeric', 'min:0'],
            'skus.*.conversion_factor' => ['required', 'numeric', 'min:0.001'], // Evitar divisiones por cero en el futuro
            'skus.*.weight' => ['required', 'numeric', 'min:0'],
            'skus.*.image' => ['nullable', 'image', 'max:2048'],
        ];
    }
}