<?php

namespace App\Http\Requests\Admin\Provider;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    /**
     * Normalización de datos antes de la validación.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'tax_id' => str_replace([' ', '-', '.'], '', $this->tax_id),
            'phone'  => str_replace([' ', '-', '(', ')'], '', $this->phone),
        ]);
    }

    public function rules(): array
    {
        // Obtenemos el UUID del proveedor desde la ruta
        $providerId = $this->route('provider')->id;

        return [
            'company_name'    => ['required', 'string', 'max:255'],
            'commercial_name' => ['nullable', 'string', 'max:255'],
            
            // Regla de Unicidad con Ignorar ID actual
            'tax_id' => [
                'required', 
                'string', 
                Rule::unique('providers', 'tax_id')->ignore($providerId)
            ],
            
            'internal_code' => [
                'nullable', 
                'string', 
                Rule::unique('providers', 'internal_code')->ignore($providerId)
            ],

            'contact_name'    => ['nullable', 'string', 'max:255'],
            'email_orders'    => ['nullable', 'email', 'max:255'],
            'phone'           => ['nullable', 'string', 'max:20'],
            'address'         => ['nullable', 'string', 'max:500'],
            'city'            => ['nullable', 'string', 'max:100'],
            'lead_time_days'  => ['required', 'integer', 'min:0'],
            'min_order_value' => ['required', 'numeric', 'min:0'],
            'credit_days'     => ['required', 'integer', 'min:0'],
            'credit_limit'    => ['required', 'numeric', 'min:0'],
            'is_active'       => ['required', 'boolean'],
            'notes'           => ['nullable', 'string'],
        ];
    }
}