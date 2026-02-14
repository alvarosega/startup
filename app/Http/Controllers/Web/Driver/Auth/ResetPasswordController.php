<?php

namespace App\Http\Controllers\Web\Driver\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Driver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Carbon\Carbon;

class ResetPasswordController extends Controller
{
    /**
     * Muestra la vista física para ingresar el código y la nueva clave
     */
    public function showResetForm($email)
    {
        return Inertia::render('Driver/Auth/ResetPassword', [
            'email' => $email
        ]);
    }

    /**
     * Valida el código y actualiza la contraseña
     */
    public function reset(Request $request)
    {
        Log::info('--- INICIO RESET PASSWORD DRIVER ---');

        $request->validate([
            'email' => 'required|email|exists:drivers,email',
            'code' => 'required|string|size:6',
            'password' => 'required|confirmed|min:8',
        ], [
            'code.required' => 'El código de seguridad es obligatorio.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.'
        ]);

        try {
            // 1. Verificar el código en la tabla específica de Drivers
            $record = DB::table('password_reset_codes_drivers')
                ->where('email', $request->email)
                ->where('token', $request->code)
                ->first();

            if (!$record) {
                Log::warning('Código inválido para driver: ' . $request->email);
                return back()->withErrors(['code' => 'El código es incorrecto o ha expirado.']);
            }

            // 2. Validar expiración (15 minutos)
            if (Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
                Log::warning('Código expirado para driver: ' . $request->email);
                return back()->withErrors(['code' => 'Este código ha expirado.']);
            }

            // 3. Actualizar Contraseña (El modelo Driver ya maneja el ID binario)
            $driver = Driver::where('email', $request->email)->first();
            $driver->update([
                'password' => Hash::make($request->password)
            ]);

            // 4. Limpiar tabla de códigos
            DB::table('password_reset_codes_drivers')->where('email', $request->email)->delete();
            
            Log::info('Contraseña actualizada con éxito para driver: ' . $request->email);

            return redirect()->route('driver.login')
                ->with('message', 'Contraseña actualizada. Ya puedes iniciar turno.');

        } catch (\Exception $e) {
            Log::error('ERROR CRÍTICO RESET DRIVER:', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Error interno en el servidor.']);
        }
    }
}