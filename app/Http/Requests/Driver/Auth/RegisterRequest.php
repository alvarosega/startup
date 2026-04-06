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
            'phone'          => $this->globalPhoneRules(),
            'email'          => $this->globalEmailRules(),
            'password'       => ['required', 'string', 'min:8', 'confirmed'],
            'first_name'     => ['required', 'string', 'max:100'],
            'last_name'      => ['required', 'string', 'max:100'],
            'ci_front'       => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
            'license_photo'  => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
            'license_number' => ['required', 'string', 'unique:driver_profiles,license_number'], 
            'license_plate'  => ['required', 'string', 'max:10'], 
            'vehicle_type'   => ['required', 'string', 'in:moto,car,truck'],
            // RECTIFICACIÓN: Validar elección de avatar
            'avatar_type'    => ['required', 'string', 'in:icon,image'],
            'avatar_source'  => ['required', 'string'],
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