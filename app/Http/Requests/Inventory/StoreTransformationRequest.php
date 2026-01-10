<?php

namespace App\Http\Requests\Inventory; // <--- ESTO ES CRÍTICO

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use App\Models\Sku;

class StoreTransformationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'branch_id' => 'required|exists:branches,id',
            'source_sku_id' => 'required|exists:skus,id',
            'destination_sku_id' => 'required|exists:skus,id|different:source_sku_id',
            'quantity' => 'required|integer|min:1', 
            'notes' => 'nullable|string'
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            // Si falta algún ID, retornamos para evitar error al buscar
            if (!$this->source_sku_id || !$this->destination_sku_id) return;

            $source = Sku::find($this->source_sku_id);
            $dest = Sku::find($this->destination_sku_id);

            if ($source && $dest) {
                if ($source->product_id !== $dest->product_id) {
                    $validator->errors()->add('destination_sku_id', 'Los SKUs deben ser del mismo Producto.');
                }
                
                // Validación de Pack a Unidad (Mayor a Menor)
                if ((float)$source->conversion_factor <= (float)$dest->conversion_factor) {
                    $validator->errors()->add('source_sku_id', 'Solo se permite transformar de Mayor Contenido a Menor.');
                }
            }
        });
    }
}