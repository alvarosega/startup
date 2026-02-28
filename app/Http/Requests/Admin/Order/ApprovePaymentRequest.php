<?php
namespace App\Http\Requests\Admin\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApprovePaymentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(['advance', 'balance'])],
            'bank_reference' => ['required', 'string', 'min:4', 'max:50'], // Trazabilidad obligatoria
        ];
    }
}