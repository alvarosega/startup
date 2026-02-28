<?php
namespace App\Http\Requests\Admin\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RejectPaymentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(['advance', 'balance'])],
            'rejection_reason' => ['required', 'string', 'min:10', 'max:255'],
        ];
    }
}