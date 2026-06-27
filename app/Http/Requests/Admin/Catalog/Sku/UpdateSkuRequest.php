<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Catalog\Sku;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Catalog\Sku;

class UpdateSkuRequest extends FormRequest
{
    /**
     * RECTIFICACIÓN: Control de acceso de privilegios de transporte.
     */
    public function authorize(): bool
    {
        return $this->user('super_admin')?->hasRole('super_admin') ?? false;
    }

    public function rules(): array
    {
        $skuModel = $this->route('sku');
        $skuId = $skuModel instanceof Sku ? $skuModel->id : $skuModel;

        return [
            'name'              => ['required', 'string', 'max:255'],
            'base_price'        => ['required', 'numeric', 'min:0'],
            'conversion_factor' => ['required', 'numeric', 'min:0.001'],
            'weight'            => ['required', 'numeric', 'min:0'],
            'is_active'         => ['required', 'boolean'],
            'image'             => ['nullable', 'image', 'mimes:webp,jpg,png', 'max:2048'],
            // RECTIFICACIÓN: Se evalúa y bloquea la mutación del código en la capa de transporte enviando un error 422 controlado
            'code'              => [
                'required', 
                'string', 
                'max:50', 
                Rule::unique('skus', 'code')->ignore($skuId)->where('deleted_epoch', 0),
                function ($attribute, $value, $fail) use ($skuModel) {
                    if ($skuModel instanceof Sku && !empty($skuModel->code) && $skuModel->code !== $value) {
                        $fail('Restricción del catálogo: El código identificador SKU es inmutable.');
                    }
                }
            ],
        ];
    }
}