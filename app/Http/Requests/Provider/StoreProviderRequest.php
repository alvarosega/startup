<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class StoreProviderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'company_name'    => ['required', 'string', 'max:255'],
            'commercial_name' => ['nullable', 'string', 'max:255'],
            'tax_id'          => ['required', 'string', 'max:20', 'unique:providers,tax_id'],
            'internal_code'   => ['nullable', 'string', 'max:50', 'unique:providers,internal_code'],
            
            'contact_name'    => ['nullable', 'string', 'max:100'],
            'email_orders'    => ['nullable', 'email', 'max:255'],
            'phone'           => ['nullable', 'string', 'max:50'],
            'address'         => ['nullable', 'string', 'max:255'],
            'city'            => ['nullable', 'string', 'max:100'],
            
            'lead_time_days'  => ['required', 'integer', 'min:0'],
            'min_order_value' => ['required', 'numeric', 'min:0'],
            'credit_days'     => ['required', 'integer', 'min:0'],
            'credit_limit'    => ['required', 'numeric', 'min:0'],
            
            'is_active'       => ['boolean'],
            'notes'           => ['nullable', 'string', 'max:1000'],
        ];
    }
}