<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\RetailMedia\Creative;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCreativeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'campaign_id'   => ['required', 'uuid', 'exists:ad_campaigns,id'],
            'placement_id'  => ['required', 'uuid', 'exists:ad_placements,id'],
            'branch_id'     => ['required', 'uuid', 'exists:branches,id'],
            'name'          => ['required', 'string', 'max:128'],
            'image_mobile'  => ['required', 'image', 'mimes:jpeg,png,webp', 'max:3072'], // Obligatorio en creación
            'image_desktop' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'action_type'   => ['required', 'string', Rule::in(['ADD_TO_CART', 'NAVIGATE'])],
            'sort_order'    => ['required', 'integer', 'min:0'],
            'is_active'     => ['required', 'boolean'],

            // Celdas planas de redirección
            'sku_id'        => ['nullable', 'uuid', 'exists:skus,id'],
            'category_id'   => ['nullable', 'uuid', 'exists:categories,id'],
            'brand_id'      => ['nullable', 'uuid', 'exists:brands,id'],
            'bundle_id'     => ['nullable', 'uuid', 'exists:bundles,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $data = $this->all();
            $actionType = $data['action_type'] ?? null;

            // Restricción 1: Si es ADD_TO_CART, obligar origen SKU o BUNDLE. Prohibir categorías o marcas.
            if ($actionType === 'ADD_TO_CART') {
                if (empty($data['sku_id']) && empty($data['bundle_id'])) {
                    $validator->errors()->add('action_type', 'VIOLACIÓN_FLUJO_COMPRA: Si la acción es ADD_TO_CART, debe proveer estrictamente un sku_id o un bundle_id.');
                }
                if (!empty($data['category_id']) || !empty($data['brand_id'])) {
                    $validator->errors()->add('action_type', 'CONTRADICCIÓN_LOGÍSTICA: No se puede ejecutar ADD_TO_CART apuntando a una categoría o marca.');
                }
            }

            // Restricción 2: Evitar ambigüedad multi-destino. Mínimo exclusividad de un campo.
            $destinations = array_filter([
                $data['sku_id'] ?? null,
                $data['category_id'] ?? null,
                $data['brand_id'] ?? null,
                $data['bundle_id'] ?? null,
            ]);

            if (count($destinations) > 1) {
                $validator->errors()->add('sku_id', 'AMBIGÜEDAD_DE_REDIRECCIÓN: Un banner no puede apuntar a múltiples destinos simultáneamente.');
            }
        });
    }
}