<?php
namespace App\Http\Requests\Admin\Provider;

use Illuminate\Foundation\Http\FormRequest;

class StoreProviderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        // Normalización Crítica: Evitamos duplicados por formato
        $this->merge([
            'tax_id' => str_replace([' ', '-', '.'], '', $this->tax_id),
            'phone'  => str_replace([' ', '-', '(', ')'], '', $this->phone),
        ]);
    }

    public function rules(): array
    {
        return [
            'company_name'    => ['required', 'string', 'max:255'],
            'commercial_name' => ['nullable', 'string', 'max:255'],
            'tax_id'          => ['required', 'string', 'unique:providers,tax_id'],
            'internal_code'   => ['nullable', 'string', 'unique:providers,internal_code'],
            'email_orders'    => ['nullable', 'email', 'max:255'],
            'phone'           => ['nullable', 'string', 'max:20'],
            'lead_time_days'  => ['integer', 'min:0'],
            'min_order_value' => ['numeric', 'min:0'],
            'is_active'       => ['boolean'],
        ];
    }
}