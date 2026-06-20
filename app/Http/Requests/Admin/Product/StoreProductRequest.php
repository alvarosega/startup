<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'max:255'],
            'brand_id'       => ['required', 'uuid', Rule::exists('brands', 'id')->where('deleted_epoch', 0)],
            'category_id'    => ['required', 'uuid', Rule::exists('categories', 'id')->where('deleted_epoch', 0)],
            'description'    => ['nullable', 'string', 'max:2000'],
            'is_active'      => ['required', 'boolean'],
            'is_alcoholic'   => ['required', 'boolean'],
            'idempotencyKey' => ['required', 'string', 'uuid'],
            'image'          => ['nullable', 'image', 'mimes:webp,jpg,png', 'max:2048'],
        ];
    }
}