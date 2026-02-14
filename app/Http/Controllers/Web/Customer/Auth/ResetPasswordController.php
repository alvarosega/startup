<?php

namespace App\Http\Controllers\Web\Customer\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
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
        return Inertia::render('Customer/Auth/ResetPassword', [
            'email' => $email
        ]);
    }

    /**
     * Valida el código y actualiza la contraseña en el Silo
     */
    public function reset(Request $request)
    {
        Log::info('--- INICIO RECUPERACIÓN: PASO 2 (UPDATE) ---');

        $request->validate([
            'email' => 'required|email|exists:customers,email',
            'code' => 'required|string|size:6',
            'password' => 'required|confirmed|min:8',
        ], [
            'code.required' => 'El código de 6 dígitos es obligatorio.',
            'password.confirmed' => 'Las contraseñas no coinciden.'
        ]);

        try {
            // 1. Buscar código en el Silo de Clientes
            $record = DB::table('password_reset_codes_customers')
                ->where('email', $request->email)
                ->where('token', $request->code)
                ->first();

            if (!$record) {
                Log::warning('Código inválido intentado para email: ' . $request->email);
                return back()->withErrors(['code' => 'El código es incorrecto o no existe.']);
            }

            // 2. Validar expiración (15 minutos)
            if (Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
                Log::warning('Código expirado para email: ' . $request->email);
                return back()->withErrors(['code' => 'Este código ha expirado.']);
            }

            // 3. Actualizar Contraseña del Cliente
            $customer = Customer::where('email', $request->email)->first();
            $customer->update([
                'password' => Hash::make($request->password)
            ]);

            // 4. Limpieza del Silo
            DB::table('password_reset_codes_customers')->where('email', $request->email)->delete();
            
            Log::info('Contraseña actualizada con éxito para el cliente: ' . $request->email);

            // 5. Redirección final al Login físico
            return redirect()->route('login')
                ->with('message', '¡Contraseña actualizada! Ya puedes ingresar.');

        } catch (\Exception $e) {
            Log::error('ERROR EN PASO 2 RECUPERACIÓN:', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Error interno al actualizar la contraseña.']);
        }
    }
}