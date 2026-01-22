<?php

namespace App\Modules\Identity\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // Recomendado para reglas unique más limpias

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $userId = $this->user()->id;
    
        return [
            // DATOS PERSONALES
            // Quitamos 'sometimes' porque el formulario Edit.vue envía todos los campos siempre.
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            
            // Fecha: Obligatoria y mayor de 18 años.
            'birth_date' => ['required', 'date', 'before:-18 years'],

            // Género: Coincide con tu select en Vue (masculino, femenino, pnd)
            'gender'     => ['nullable', 'in:masculino,femenino,pnd'],
            
            // EMAIL (CRÍTICO)
            // Cambiado a 'required'. No permitas nulos aquí.
            'email'      => [
                'required', 
                'email', 
                'max:255', 
                // Sintaxis robusta que ignora el ID del usuario actual
                Rule::unique('users')->ignore($userId), 
            ],
            
            // DATOS DE REPARTIDOR (Opcionales)
            'license_number' => ['nullable', 'string', 'max:50'],
            'vehicle_type'   => ['nullable', 'in:Moto,Automóvil,Camioneta,Bicicleta/Pie'],
            'license_plate'  => ['nullable', 'string', 'max:15'],
        ];
    }

    public function messages(): array
    {
        return [
            'birth_date.required' => 'La fecha de nacimiento es necesaria.',
            'birth_date.before'   => 'Debes ser mayor de 18 años para registrarte.',
            'first_name.required' => 'El nombre es obligatorio.',
            'last_name.required'  => 'El apellido es obligatorio.',
            'email.required'      => 'El correo electrónico es obligatorio para tu cuenta.',
            'email.unique'        => 'Este correo ya está registrado por otro usuario.',
            'email.email'         => 'Ingresa un correo electrónico válido.',
        ];
    }
}