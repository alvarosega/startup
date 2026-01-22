<?php

namespace App\Modules\Identity\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $userId = $this->user()->id;
    
        return [
            // Nivel 1: 'sometimes' permite guardar solo estos 3 al inicio
            'first_name' => ['sometimes', 'required', 'string', 'max:100'],
            'last_name'  => ['sometimes', 'required', 'string', 'max:100'],
            'birth_date' => ['sometimes', 'required', 'date', 'before:-18 years'],

            // Nivel 2: 'nullable' evita el error de "invalid" si el campo está vacío
            'gender'     => ['nullable', 'in:masculino,femenino,pnd'],
            'email'      => ['sometimes', 'nullable', 'email', 'unique:users,email,' . $userId],
            
            // Datos de Repartidor
            'license_number' => ['sometimes', 'nullable', 'string'],
            'vehicle_type'   => ['sometimes', 'nullable', 'in:Moto,Automóvil,Camioneta,Bicicleta/Pie'],
            'license_plate'  => ['sometimes', 'nullable', 'string', 'max:15'],
        ];
    }


    public function messages(): array
    {
        return [
            'birth_date.before' => 'Debes ser mayor de 18 años para operar en la plataforma.',
            'first_name.required' => 'El nombre es obligatorio.',
            'last_name.required' => 'El apellido es obligatorio.',
        ];
    }
}