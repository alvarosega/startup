<?php

namespace App\Actions\Driver\Auth;

use Illuminate\Support\Facades\{DB, Mail};
use App\Mail\Driver\DriverResetCodeMail;
use Carbon\Carbon;

class SendResetCodeAction
{
    public function execute(string $email): void
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_reset_codes_drivers')->updateOrInsert(
            ['email' => $email],
            ['token' => $code, 'created_at' => Carbon::now()]
        );

        Mail::to($email)->send(new DriverResetCodeMail($code));
    }
}