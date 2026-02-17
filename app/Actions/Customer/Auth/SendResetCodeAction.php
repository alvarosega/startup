<?php

namespace App\Actions\Customer\Auth;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\Customer\CustomerResetCodeMail;
use Carbon\Carbon;

class SendResetCodeAction
{
    /**
     * Genera un código de 6 dígitos, lo persiste en el silo y envía el mail.
     */
    public function execute(string $email): void
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Persistencia atómica en la tabla del Silo
        DB::table('password_reset_codes_customers')->updateOrInsert(
            ['email' => $email],
            [
                'token' => $code, 
                'created_at' => Carbon::now()
            ]
        );

        Mail::to($email)->send(new CustomerResetCodeMail($code));
    }
}