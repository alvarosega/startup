<?php

namespace App\Http\Requests\Driver\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\ValidatesGlobalIdentity;

class RegisterRequest extends FormRequest
{
    use ValidatesGlobalIdentity;

    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $this->normalizeIdentityData();
    }


    public function rules(): array
    {
        return [
            'phone'              => $this->globalPhoneRules('drivers'),
            'email'              => $this->globalEmailRules('drivers'),
            'password'           => ['required', 'string', 'min:8', 'confirmed'],
            'first_name'         => ['required', 'string', 'max:100'],
            'last_name'          => ['required', 'string', 'max:100'],
            'ci_front'           => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
            'license_photo'      => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
            'vehicle_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
            'license_number'     => ['required', 'string', 'max:50', 'unique:driver_profiles,license_number'], 
            'license_plate'      => ['required', 'string', 'max:10'], 
            'vehicle_type'       => ['required', 'string', 'in:moto,car,truck'],
            'avatar_type'        => ['nullable', 'string', 'max:50'],
            'avatar_source'      => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'license_number.unique' => 'Este número de licencia ya está registrado en nuestra base de datos.',
            'vehicle_type.in'       => 'El tipo de vehículo seleccionado no es válido.',
        ];
    }
}