<?php

namespace App\Actions\Driver\Auth;

use App\DTOs\Driver\Auth\ResetPasswordData;
use App\Models\Driver;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ResetPasswordAction
{
    public function execute(ResetPasswordData $data): void
    {
        $resetRecord = DB::table('password_reset_codes_drivers')
            ->where('email', $data->email)
            ->where('token', $data->code)
            ->first();

        if (! $resetRecord) {
            throw ValidationException::withMessages([
                'code' => 'El código proporcionado es inválido o ha expirado.',
            ]);
        }

        DB::transaction(function () use ($data) {
            // Se instancia el modelo para forzar la ejecución del cast 'hashed'
            $driver = Driver::where('email', $data->email)->firstOrFail();
            
            $driver->update([
                'password' => $data->password, 
            ]);

            DB::table('password_reset_codes_drivers')->where('email', $data->email)->delete();
        });
    }
}