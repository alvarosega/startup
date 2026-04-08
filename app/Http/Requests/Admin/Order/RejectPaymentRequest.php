<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RejectPaymentRequest extends FormRequest
{
    public function authorize(): bool { return auth()->guard('super_admin')->check(); }

    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'min:10', 'max:255'],
            // LEY DE DECISIÓN: El admin decide el destino de la orden
            'rejection_action' => ['required', 'string', Rule::in(['cancel', 'retry'])]
        ];
    }
}