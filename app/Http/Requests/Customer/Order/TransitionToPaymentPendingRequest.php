<?php

declare(strict_types=1);

namespace App\Http\Requests\Customer\Order;

use Illuminate\Foundation\Http\FormRequest;

class TransitionToPaymentPendingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->guard('customer')->check();
    }

    public function rules(): array
    {
        return [
            // Límite estricto: 2048 KB = 2 MB. Tipos seguros para lectura manual.
            'proof' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:2048'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'proof.required' => 'El comprobante es obligatorio.',
            'proof.mimes'    => 'Formato inválido. Solo JPG, PNG o PDF.',
            'proof.max'      => 'El archivo no debe exceder los 2MB de peso.',
        ];
    }
}