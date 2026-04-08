<?php

declare(strict_types=1);

namespace App\Http\Resources\Customer\Orders;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderPendingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $now = now();
        $expiresAt = $this->reservation_expires_at;
        
        // Cálculo estricto: Si ya expiró, devuelve 0 para que el Frontend dispare el reload y el Backend lo rechace.
        $secondsRemaining = ($expiresAt && $expiresAt > $now) 
            ? $expiresAt->diffInSeconds($now) 
            : 0;

        return [
            'order' => [
                'id'           => (string) $this->id,
                'code'         => (string) $this->code,
                'total_amount' => (float) $this->total_amount,
                'status'       => (string) $this->status,
            ],
            'payment_context' => [
                'seconds_remaining' => $secondsRemaining,
                // Estos datos deberían provenir idealmente de una tabla de configuración financiera,
                // se inyectan estáticos por ahora según su diseño de Vue.
                'qr_image'          => asset('assets/img/static_qr_payment.png'),
                'bank_name'         => 'Banco Unión / BNB',
            ]
        ];
    }
}