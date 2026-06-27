<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Catalog\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    /**
     * RECTIFICACIÓN: Control de acceso restrictivo basado en guard de sesión administrativo.
     */
    public function authorize(): bool
    {
        return $this->user('super_admin')?->hasRole('super_admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:255'],
            'brand_id'        => ['required', 'string', Rule::exists('brands', 'id')->where('deleted_epoch', 0)],
            'category_id'     => ['required', 'string', Rule::exists('categories', 'id')->where('deleted_epoch', 0)],
            'description'     => ['nullable', 'string', 'max:2000'],
            'is_active'       => ['required', 'boolean'],
            'is_alcoholic'    => ['required', 'boolean'],
            'idempotency_key' => ['required', 'string', 'uuid'],
            'image'           => ['nullable', 'image', 'mimes:webp,jpg,png', 'max:2048'],
        ];
    }
}