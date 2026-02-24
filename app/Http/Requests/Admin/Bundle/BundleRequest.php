<?php

namespace App\Http\Requests\Admin\Bundle;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class BundleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // La autorización real la hace el Middleware/Policy
    }

    protected function prepareForValidation()
    {
        // Generamos el slug aquí para validarlo antes de llegar a la BD
        if ($this->has('name')) {
            $this->merge([
                'slug' => Str::slug($this->name)
            ]);
        }
    }

    public function rules(): array
    {
        // Obtenemos el ID de la sucursal del usuario actual
        $branchId = $this->input('branch_id'); 
        $bundleId = $this->route('bundle')?->id;
        
        // Si estamos editando, obtenemos el ID del bundle actual para excluirlo
        return [
            'branch_id' => 'required|exists:branches,id', // <--- NUEVO CAMPO REQUERIDO
            'name' => [
                'required', 'string', 'max:255',
                // Validar unicidad combinando nombre + sucursal SELECCIONADA
                Rule::unique('bundles', 'slug')
                    ->where('branch_id', $branchId) 
                    ->ignore($bundleId)
            ],
            'description' => 'nullable|string|max:1000',
            'fixed_price' => 'nullable|numeric|min:0',
            'is_active'   => 'boolean',
            'image'       => 'nullable|image|max:2048', // 2MB
            
            // Validación de Items
            'items'             => 'required|array|min:1',
            'items.*.sku_id'    => 'required|exists:skus,id',
            'items.*.quantity'  => 'required|integer|min:1',
        ];
    }

    public function messages()
    {
        return [
            'name.unique' => 'Ya existe un pack con este nombre en tu sucursal.',
            'items.required' => 'Debes agregar al menos un producto al pack.',
        ];
    }
}