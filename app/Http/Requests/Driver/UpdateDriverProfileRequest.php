<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDriverProfileRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $driverId = $this->user()->driverProfile?->id;

        return [
            'license_number' => [
                'required', 'string', 'max:20', 
                Rule::unique('driver_profiles', 'license_number')->ignore($driverId)
            ],
            'license_plate' => ['required', 'string', 'max:15'],
            'vehicle_type' => ['required', 'in:moto,car,truck'],
        ];
    }
}