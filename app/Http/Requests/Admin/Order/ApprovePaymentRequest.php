<?php
namespace App\Http\Requests\Admin\Order;

use Illuminate\Foundation\Http\FormRequest;

class ApprovePaymentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'bank_reference' => ['required', 'string', 'min:4', 'max:50'], 
        ];
    }
}