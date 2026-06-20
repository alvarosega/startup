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
        $code = (string) random_int(100000, 999999);
    
        \Illuminate\Support\Facades\DB::table('password_reset_codes_drivers')->updateOrInsert(
            ['email' => $data->email],
            [
                'token' => \Illuminate\Support\Facades\Hash::make($code), // Cifrado obligatorio
                'created_at' => now(),
            ]
        );
    
        \Illuminate\Support\Facades\Mail::to($data->email)->send(new DriverResetCodeMail($code));
    }
}