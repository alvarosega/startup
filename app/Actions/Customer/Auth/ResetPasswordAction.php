<?php

namespace App\Actions\Customer\Auth;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class ResetPasswordAction // <--- CORREGIDO: Debe coincidir con el nombre del archivo
{
    /**
     * Valida el código, actualiza la clave y limpia el silo.
     */
    public function execute(string $email, string $code, string $password): void
    {
        DB::transaction(function () use ($email, $code, $password) {
            $record = DB::table('password_reset_codes_customers')
                ->where('email', $email)
                ->where('token', $code)
                ->first();

            // Validación de integridad y expiración (15 min)
            if (!$record || Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
                throw ValidationException::withMessages([
                    'code' => 'El código es incorrecto o ha expirado.',
                ]);
            }

            // Actualización del modelo del Silo
            $customer = Customer::where('email', $email)->first();
            
            if (!$customer) {
                throw ValidationException::withMessages([
                    'email' => 'No se encontró el usuario.',
                ]);
            }

            $customer->update([
                'password' => Hash::make($password)
            ]);

            // Limpieza del Silo
            DB::table('password_reset_codes_customers')->where('email', $email)->delete();
        });
    }
}