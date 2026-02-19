<?php

namespace App\Actions\Driver\Auth;

use App\Models\Driver;
use Illuminate\Support\Facades\{DB, Hash};
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class ResetPasswordAction
{
    public function execute(string $email, string $code, string $password): void
    {
        $record = DB::table('password_reset_codes_drivers')
            ->where('email', $email)
            ->where('token', $code)
            ->first();

        if (!$record || Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
            throw ValidationException::withMessages([
                'code' => 'El código de seguridad es incorrecto o ha expirado.',
            ]);
        }

        $driver = Driver::where('email', $email)->firstOrFail();
        $driver->update([
            'password' => Hash::make($password)
        ]);

        DB::table('password_reset_codes_drivers')->where('email', $email)->delete();
    }
}