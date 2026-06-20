<?php

declare(strict_types=1);

namespace App\Http\Resources\Customer\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class OrderPendingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // 1. Obtener el timestamp absoluto de ahora (independiente de la zona horaria)
        $nowTimestamp = Carbon::now()->getTimestamp();
        
        // 2. Parsear la fecha de la base de datos y forzar su lectura como timestamp absoluto
        // El operador null safe evita errores si por alguna razón la fecha es nula
        $expiresAtTimestamp = Carbon::parse($this->reservation_expires_at)->getTimestamp();

        // 3. Cálculo matemático estricto (Segundos totales)
        $diff = $expiresAtTimestamp - $nowTimestamp;

        // 4. Sanitización: Si es negativo (ya pasó), devolver 0.
        $secondsRemaining = $diff > 0 ? $diff : 0;

        return [
            'order' => [
                'id'           => (string) $this->id,
                'code'         => (string) $this->code,
                'total_amount' => (float) $this->total_amount,
                'status'       => (string) $this->status,
            ],
            'payment_context' => [
                'seconds_remaining' => $secondsRemaining,
                'qr_image'          => asset('assets/img/static_qr_payment.png'),
                'bank_name'         => 'Banco Unión / BNB',
            ]
        ];
    }
}