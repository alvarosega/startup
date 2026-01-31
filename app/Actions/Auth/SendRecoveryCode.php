<?php

namespace App\Actions\Auth;

use App\DTOs\Auth\ForgotPasswordData;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetCodeMail; // <--- IMPORTANTE: Importar tu Mailable

class SendRecoveryCode
{
    public function execute(ForgotPasswordData $data): void
    {
        // 1. Generar código de 6 dígitos
        $code = rand(100000, 999999);

        // 2. Guardar en caché por 15 minutos (Para coincidir con tu mail)
        // Key: password_reset_{email}
        Cache::put('password_reset_' . $data->email, $code, now()->addMinutes(15));

        // 3. Enviar el Email (AQUÍ ESTABA EL PROBLEMA)
        // Usamos tu clase ResetCodeMail pasando el $code
        Mail::to($data->email)->send(new ResetCodeMail($code));
    }
}