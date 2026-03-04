<?php
namespace App\Http\Requests\Admin\Order;

use Illuminate\Foundation\Http\FormRequest;

class RejectPaymentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'min:10', 'max:255'],
        ];
    }
}