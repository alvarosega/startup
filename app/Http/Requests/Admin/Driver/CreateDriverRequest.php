<?php

namespace App\Http\Requests\Admin\Driver;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\ValidatesGlobalIdentity;

class CreateDriverRequest extends FormRequest
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
            'first_name'     => ['required', 'string', 'max:100'],
            'last_name'      => ['required', 'string', 'max:100'],
            'phone'          => $this->globalPhoneRules(),
            'password'       => ['required', 'string', 'min:8'],
            'license_number' => ['required', 'string', 'max:50'],
            'license_plate'  => ['required', 'string', 'max:20'],
            'vehicle_type'   => ['required', 'string', 'in:moto,car,truck'],
        ];
    }
}