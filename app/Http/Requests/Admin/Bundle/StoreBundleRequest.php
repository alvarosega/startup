<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Bundle;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBundleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:128'],
            'image'        => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'type'         => ['required', 'string', Rule::in(['OFFER', 'TEMPLATE'])],
            'starts_at'    => ['nullable', 'date'],
            'ends_at'      => ['nullable', 'date', 'after:starts_at'],
            'is_active'    => ['required', 'boolean'],
            
            // Validación de los ítems componentes
            'items'          => ['required', 'array', 'min:1'],
            'items.*.sku_id' => ['required', 'uuid', Rule::exists('skus', 'id')],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
        ];
    }
}