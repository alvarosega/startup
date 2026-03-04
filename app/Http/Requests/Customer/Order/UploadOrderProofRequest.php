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
            'proof' => ['required', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:2048'] // 2MB Max
        ];
    }
    
    public function messages(): array
    {
        return [
            'proof.required' => 'Debes adjuntar un archivo.',
            'proof.mimes' => 'El comprobante debe ser una imagen (PNG/JPG) o un PDF.',
            'proof.max' => 'El archivo no debe pesar más de 2MB.',
        ];
    }
}