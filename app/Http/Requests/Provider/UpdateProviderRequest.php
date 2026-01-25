<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProviderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        // Obtenemos el ID del proveedor de la ruta para ignorarlo en unique
        $providerId = $this->route('provider')->id;

        return [
            'company_name'    => ['required', 'string', 'max:255'],
            'commercial_name' => ['nullable', 'string', 'max:255'],
            'tax_id'          => ['required', 'string', 'max:20', Rule::unique('providers')->ignore($providerId)],
            'internal_code'   => ['nullable', 'string', 'max:50', Rule::unique('providers')->ignore($providerId)],
            'email_orders'    => ['nullable', 'email'],
            'phone'           => ['nullable', 'string', 'max:20'],
            'contact_name'    => ['nullable', 'string', 'max:100'],
            'address'         => ['nullable', 'string', 'max:255'],
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