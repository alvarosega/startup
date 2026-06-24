<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Users\Driver;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\ValidatesGlobalIdentity;

class StoreDriverRequest extends FormRequest
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
            'first_name'     => ['required', 'string', 'max:255'],
            'last_name'      => ['required', 'string', 'max:255'],
            'email'          => $this->globalEmailRules(),
            'phone'          => $this->globalPhoneRules(),
            'branch_id'      => ['required', 'uuid', 'exists:branches,id'],
            'status'         => ['required', 'string', 'in:pending,approved,rejected,suspended'],
            'license_number' => ['required', 'string', 'max:255', 'unique:driver_profiles,license_number'],
            'license_plate'  => ['required', 'string', 'max:10'],
            'vehicle_type'   => ['required', 'string', 'max:255']
        ];
    }
}