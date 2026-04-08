<?php

declare(strict_types=1);

namespace App\Http\Requests\Driver\Order;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class AcceptOrderRequest extends FormRequest
{
    public function authorize(): bool 
    { 
        return auth()->guard('driver')->check(); 
    }

    public function rules(): array 
    { 
        // No hay campos en el body del POST, la validación es de estado
        return []; 
    }

    /**
     * Validaciones complejas "After" (Después de las reglas base)
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $driver = Auth::guard('driver')->user();

            // BLOQUEO: Verificar si el conductor ya tiene un pedido activo (en curso)
            $hasActiveOrder = Order::where('driver_id', $driver->id)
                ->whereIn('status', ['preparing', 'ready_for_dispatch', 'dispatched', 'arrived'])
                ->exists();

            if ($hasActiveOrder) {
                $validator->errors()->add('order', 'Operación denegada. Ya tienes una carga asignada en curso. Finalízala antes de tomar otra.');
            }
        });
    }
}