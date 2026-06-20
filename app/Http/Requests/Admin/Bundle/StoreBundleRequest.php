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
            'type'         => ['required', 'string', Rule::in(['OFFER', 'TEMPLATE'])],
            'is_active'    => ['required', 'boolean'],
            'image'        => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'sku_ids'      => ['required', 'array', 'min:1'],
            'sku_ids.*'    => ['required', 'uuid', 'exists:skus,id'],
        ];
    }
}