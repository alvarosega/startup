<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Users\Customer;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\ValidatesGlobalIdentity;
use App\Rules\GlobalUniqueIdentity;

class StoreCustomerRequest extends FormRequest
{
    use ValidatesGlobalIdentity;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeIdentityData();
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => array_merge($this->globalEmailRules(), [new GlobalUniqueIdentity()]),
            'phone'      => array_merge($this->globalPhoneRules(), [new GlobalUniqueIdentity()]),
            'branch_id'  => ['nullable', 'uuid', 'exists:branches,id'],
            'is_active'  => ['required', 'boolean'],
        ];
    }
}