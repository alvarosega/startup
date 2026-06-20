<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\RetailMedia;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCreativeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'campaign_id'   => ['required', 'uuid', 'exists:ad_campaigns,id'],
            'placement_id'  => ['required', 'uuid', 'exists:ad_placements,id'],
            'branch_id'     => ['required', 'uuid', 'exists:branches,id'],
            
            // Llaves de anclaje contextual plano
            'sku_id'        => ['nullable', 'uuid', 'exists:skus,id'],
            'category_id'   => ['nullable', 'uuid', 'exists:categories,id'],
            'bundle_id'     => ['nullable', 'uuid', 'exists:bundles,id'],
            'brand_id'      => ['nullable', 'uuid', 'exists:brands,id'],
            
            // Destino polimórfico interno
            'target_id'     => ['required', 'uuid'],
            'target_type'   => ['required', 'string', Rule::in(['sku', 'category', 'bundle'])],
            
            'name'          => ['required', 'string', 'max:128'],
            'image_mobile'  => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'image_desktop' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:4096'],
            'action_type'   => ['required', 'string', Rule::in(['ADD_TO_CART', 'NAVIGATE'])],
            'sort_order'    => ['required', 'integer', 'min:0'],
            'is_active'     => ['required', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // 1. Validación de Exclusión Mutua para puntos de anclaje
            $anchors = array_filter($this->only(['sku_id', 'category_id', 'bundle_id', 'brand_id']));
            if (count($anchors) !== 1) {
                $validator->errors()->add('sku_id', 'La pieza creativa debe anclarse a un único criterio (SKU, Categoría, Combo o Marca).');
            }

            // 2. Validación de Coexistencia Asimétrica de Multimedia (Solo en creación)
            if ($this->isMethod('POST')) {
                if (!$this->hasFile('image_mobile') && !$this->hasFile('image_desktop')) {
                    $validator->errors()->add('image_mobile', 'Debe proveer al menos una pieza gráfica (versión móvil o escritorio) para inicializar el creativo.');
                }
            }

            // 3. Validación de integridad existencial del target polimórfico
            if ($targetType = $this->input('target_type')) {
                $targetId = $this->input('target_id');
                $tableMap = ['sku' => 'skus', 'category' => 'categories', 'bundle' => 'bundles'];
                
                if ($targetId && isset($tableMap[$targetType])) {
                    $exists = \Illuminate\Support\Facades\DB::table($tableMap[$targetType])
                        ->where('id', $targetId)
                        ->exists();
                    if (!$exists) {
                        $validator->errors()->add('target_id', "El ID de destino especificado no existe en la entidad relacional de {$targetType}s.");
                    }
                }
            }
        });
    }
}