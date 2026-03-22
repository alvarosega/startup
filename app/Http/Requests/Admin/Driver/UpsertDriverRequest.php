<?php

namespace App\Http\Requests\Admin\Driver;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\ValidatesGlobalIdentity;
use Illuminate\Validation\Rule;

class UpsertDriverRequest extends FormRequest
{
    use ValidatesGlobalIdentity;

    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void 
    {
        $this->normalizeIdentityData();
    }

    public function rules(): array
    {
        $driverId = $this->route('driver');
    
        return [
            'branch_id'      => ['required', 'uuid', 'exists:branches,id'], // Siempre obligatorio ahora
            'status'         => ['required', 'in:pending,active,inactive,rejected'],
            'first_name'     => ['required', 'string'],
            'last_name'      => ['required', 'string'],
            'phone'          => ['required', 'string', Rule::unique('drivers')->ignore($driverId)],
            'email'          => ['required', 'email', Rule::unique('drivers')->ignore($driverId)],
            'license_number' => ['required', 'string'],
            'license_plate'  => ['required', 'string'],
            'vehicle_type'   => ['required', 'in:moto,car,truck'],
            'rejection_reason' => ['nullable', 'string'],
        ];
    }
}