<?php

namespace App\Http\Requests\Admin\Provider;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProviderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $providerId = $this->route('provider')->id;

        return [
            'company_name'    => ['required', 'string', 'max:255'],
            'commercial_name' => ['nullable', 'string', 'max:255'],
            'tax_id'          => ['required', 'string', Rule::unique('providers')->ignore($providerId)],
            'internal_code'   => ['nullable', 'string', Rule::unique('providers')->ignore($providerId)],
            'contact_name'    => ['nullable', 'string', 'max:255'],
            'email_orders'    => ['nullable', 'email', 'max:255'],
            'phone'           => ['nullable', 'string', 'max:20'],
            'address'         => ['nullable', 'string', 'max:500'],
            'city'            => ['nullable', 'string', 'max:100'],
            'lead_time_days'  => ['integer', 'min:1'],
            'min_order_value' => ['numeric', 'min:0'],
            'credit_days'     => ['integer', 'min:0'],
            'credit_limit'    => ['numeric', 'min:0'],
            'is_active'       => ['boolean'],
            'notes'           => ['nullable', 'string'],
        ];
    }
}