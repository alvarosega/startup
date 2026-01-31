<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Sku;

class UpsertProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // Obtener ID para ignorar en validación unique (si es update)
        $productId = $this->route('product')?->id;

        return [
            // --- PRODUCTO MAESTRO ---
            'name' => "required|string|max:255|unique:products,name,{$productId},id",
            'brand_id' => 'required|exists:brands,id',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'is_alcoholic' => 'boolean',
            'image' => 'nullable|image|max:2048', // Imagen Principal

            // --- VARIANTS (SKUS) ---
            'skus' => 'required|array|min:1',
            
            // Validación de estructura de cada SKU
            'skus.*.id' => 'nullable|uuid',
            'skus.*.name' => 'required|string|max:255',
            
            // Validación de Código EAN (Crítico para evitar Error 500)
            'skus.*.code' => [
                'nullable',
                'string',
                'max:50',
                'distinct', // 1. Evita duplicados en el array enviado (Frontend)
                function ($attribute, $value, $fail) {
                    // 2. Validación contra la Base de Datos
                    if (empty($value)) return;

                    // Extraer índice para saber si es un SKU existente (tiene ID) o nuevo
                    // formato atributo: "skus.0.code"
                    $index = explode('.', $attribute)[1];
                    $skuId = $this->input("skus.{$index}.id");

                    // CRÍTICO: Usar withTrashed() porque la BD tiene Unique Index físico
                    // Si no usamos withTrashed, Laravel no ve los borrados, intenta insertar y MySQL explota.
                    $query = Sku::withTrashed()->where('code', $value);
                    
                    if ($skuId) {
                        // Si estamos editando, ignoramos el propio ID
                        $query->where('id', '!=', $skuId);
                    }

                    if ($query->exists()) {
                        $fail("El código '{$value}' ya está registrado (incluso si está archivado).");
                    }
                },
            ],

            // Datos Numéricos
            'skus.*.price' => 'required|numeric|min:0',
            'skus.*.conversion_factor' => [
                'required',
                'numeric',
                'min:0.001', // Mínimo más pequeño si usas 3 decimales
                'max:9999999.999', // Ajustar según la nueva precisión
                'regex:/^\d+(\.\d{1,3})?$/' // Limitar a 3 decimales
            ],
            'skus.*.weight' => 'nullable|numeric|min:0',
            'skus.*.price' => ['required', 'numeric', 'min:0'],
            // Imagen por SKU (Opcional)
            'skus.*.image' => 'nullable|image|max:2048',
        ];
    }

    /**
     * Mensajes personalizados de error.
     */
    public function messages(): array
    {
        return [
            'skus.*.code.distinct' => 'El código :input está duplicado dentro de este mismo formulario.',
            'skus.*.name.required' => 'El nombre de la variante es obligatorio.',
            'skus.*.price.required' => 'El precio de la variante es obligatorio.',
            'skus.*.image.image' => 'El archivo debe ser una imagen válida (jpg, png, webp).',
            'skus.*.image.max' => 'La imagen no debe pesar más de 2MB.',
        ];
    }
}