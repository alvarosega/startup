<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Operations\Provider;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name'    => ['required', 'string', 'max:255'],
            'commercial_name' => ['nullable', 'string', 'max:255'],
            'tax_id'          => ['required', 'string', Rule::unique('providers', 'tax_id')->where('deleted_epoch', 0)], 
            'internal_code'   => ['nullable', 'string', Rule::unique('providers', 'internal_code')->where('deleted_epoch', 0)],
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