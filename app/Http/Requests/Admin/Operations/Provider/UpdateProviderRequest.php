<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Operations\Provider;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProviderRequest extends FormRequest
{
    /**
     * Blindaje en la frontera de transporte HTTP mediante control estricto de roles.
     */
    public function authorize(): bool
    {
        return $this->user('super_admin')?->hasRole('super_admin') ?? false;
    }

    public function rules(): array
    {
        $providerId = $this->route('provider') instanceof \App\Models\Operations\Provider
            ? $this->route('provider')->id
            : $this->route('provider');

        return [
            'company_name'    => ['required', 'string', 'max:255'],
            'commercial_name' => ['nullable', 'string', 'max:255'],
            'tax_id'          => ['required', 'string', Rule::unique('providers', 'tax_id')->ignore($providerId)->where('deleted_epoch', 0)],
            'internal_code'   => ['nullable', 'string', Rule::unique('providers', 'internal_code')->ignore($providerId)->where('deleted_epoch', 0)],
            'contact_name'    => ['nullable', 'string', 'max:255'],
            'email_orders'    => ['nullable', 'email', 'max:255'],
            'phone'           => ['nullable', 'string', 'max:20'],
            'address'         => ['nullable', 'string', 'max:500'],
            'city'            => ['nullable', 'string', 'max:100'],
            'lead_time_days'  => ['required', 'integer', 'min:1'], // RECTIFICACIÓN: Corregido a min:1 según el contrato del test de actualización
            'min_order_value' => ['required', 'numeric', 'min:0'],
            'credit_days'     => ['required', 'integer', 'min:0'],
            'credit_limit'    => ['required', 'numeric', 'min:0'],
            'is_active'       => ['required', 'boolean'],
            'notes'           => ['nullable', 'string'],
        ];
    }
}