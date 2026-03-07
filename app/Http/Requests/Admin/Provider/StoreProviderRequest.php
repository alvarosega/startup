<?php

namespace App\Http\Requests\Admin\Provider;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\ValidatesGlobalIdentity;

class StoreProviderRequest extends FormRequest
{
    use ValidatesGlobalIdentity;

    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        // REGLA 1.A: Normalización obligatoria antes de validar
        $this->normalizeIdentityData();
    }

    public function rules(): array
    {
        return [
            'company_name'    => ['required', 'string', 'max:255'],
            'commercial_name' => ['nullable', 'string', 'max:255'],
            'tax_id'          => ['required', 'string', 'unique:providers,tax_id'],
            'internal_code'   => ['nullable', 'string', 'unique:providers,internal_code'],
            'contact_name'    => ['nullable', 'string', 'max:255'],
            'email_orders'    => ['required', 'email', 'max:255'],
            'phone'           => ['nullable', 'string', 'max:20'],
            'address'         => ['nullable', 'string', 'max:500'],
            'city'            => ['nullable', 'string', 'max:100'],
            'lead_time_days'  => ['required', 'integer', 'min:1'],
            'min_order_value' => ['required', 'numeric', 'min:0'],
            'credit_days'     => ['required', 'integer', 'min:0'],
            'credit_limit'    => ['required', 'numeric', 'min:0'],
            'is_active'       => ['boolean'],
            'notes'           => ['nullable', 'string'],
        ];
    }
}