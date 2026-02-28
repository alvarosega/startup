<?php

namespace App\Actions\Driver\Auth;

use App\DTOs\Driver\Auth\SendResetCodeData;
use App\Mail\Driver\DriverResetCodeMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendResetCodeAction
{
    public function execute(SendResetCodeData $data): void
    {
        $code = strtoupper(Str::random(6)); // Convertido a mayúsculas para mejor legibilidad

        DB::table('password_reset_codes_drivers')->updateOrInsert(
            ['email' => $data->email],
            [
                'token' => $code,
                'created_at' => now(),
            ]
        );

        // Envío del correo usando tu clase Mailable
        Mail::to($data->email)->send(new DriverResetCodeMail($code));
    }
}