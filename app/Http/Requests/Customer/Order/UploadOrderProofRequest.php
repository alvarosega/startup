<?php

namespace App\Http\Requests\Customer\Order;

use Illuminate\Foundation\Http\FormRequest;

class UploadOrderProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }


    public function rules(): array
    {
        return [
            // RECTIFICACIÓN: Aumentamos a 5MB (5120) para evitar rebotes de validación
            'proof' => ['required', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:5120'] 
        ];
    }

    public function messages(): array
    {
        return [
            'proof.required' => 'Debes adjuntar un archivo.',
            'proof.mimes'    => 'Usa PNG, JPG o PDF.',
            'proof.max'      => 'El archivo no debe pesar más de 5MB.',
        ];
}
}