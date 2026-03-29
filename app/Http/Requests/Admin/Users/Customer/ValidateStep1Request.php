<?php

namespace App\Http\Requests\Admin\Users\Customer;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\ValidatesGlobalIdentity;

class ValidateStep1Request extends FormRequest
{
    use ValidatesGlobalIdentity;

    public function authorize(): bool
    {
        // Solo el personal administrativo con guard super_admin puede disparar esto
        return auth()->guard('super_admin')->check();
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeIdentityData();
    }

    public function rules(): array
    {
        return [
            'first_name'   => ['required', 'string', 'max:100'],
            'last_name'    => ['required', 'string', 'max:100'],
            'country_code' => ['required', 'string', 'size:2'],
            'email'        => $this->globalEmailRules(), // Mantiene unicidad global
            'phone'        => $this->globalPhoneRules(), // Mantiene formato global
            'password'     => ['required', 'string', 'min:6'], // Sin confirmation para rapidez de Admin
        ];
    }
}