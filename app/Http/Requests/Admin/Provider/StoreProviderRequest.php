<?php

namespace App\Http\Requests\Admin\Provider;

use Illuminate\Foundation\Http\FormRequest;

class StoreProviderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'company_name'    => ['required', 'string', 'max:255'],
            'commercial_name' => ['nullable', 'string', 'max:255'],
            // Regla crítica: El NIT debe ser único en la tabla de proveedores
            'tax_id'          => ['required', 'string', 'unique:providers,tax_id'], 
            'internal_code'   => ['nullable', 'string', 'unique:providers,internal_code'],
            'contact_name'    => ['nullable', 'string', 'max:255'],
            'email_orders'    => ['nullable', 'email', 'max:255'], // Hecho nullable (no siempre lo tienen de inmediato)
            'phone'           => ['nullable', 'string', 'max:20'],
            'address'         => ['nullable', 'string', 'max:500'],
            'city'            => ['nullable', 'string', 'max:100'],
            'lead_time_days'  => ['required', 'integer', 'min:0'],
            'min_order_value' => ['required', 'numeric', 'min:0'],
            'credit_days'     => ['required', 'integer', 'min:0'],
            'credit_limit'    => ['required', 'numeric', 'min:0'],
            'is_active'       => ['boolean'],
            'notes'           => ['nullable', 'string'],
        ];
    }
}