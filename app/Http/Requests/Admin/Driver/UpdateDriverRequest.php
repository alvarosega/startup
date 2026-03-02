<?php

namespace App\Http\Requests\Admin\Driver;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_id'      => ['nullable', 'uuid', 'exists:branches,id'],
            'first_name'           => ['required', 'string', 'max:100'],
            'last_name'            => ['required', 'string', 'max:100'],
            'license_number'       => ['required', 'string', 'max:50'],
            'license_plate'        => ['required', 'string', 'max:20'],
            'vehicle_type'         => ['required', 'string', 'in:moto,car,truck'],
            'is_identity_verified' => ['boolean'],
            'is_active'            => ['boolean'],
        ];
    }
}