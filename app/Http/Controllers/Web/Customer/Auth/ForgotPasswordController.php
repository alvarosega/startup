<?php

namespace App\Http\Controllers\Web\Customer\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\Customer\CustomerResetCodeMail;
use Inertia\Inertia;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    /**
     * Muestra la vista física para ingresar el correo
     */
    public function showLinkRequestForm()
    {
        return Inertia::render('Customer/Auth/ForgotPassword');
    }

    /**
     * Procesa la solicitud y redirige al paso del código
     */
    public function sendResetCode(Request $request)
    {
        Log::info('--- INICIO RECUPERACIÓN: PASO 1 (EMAIL) ---');

        $request->validate(['email' => 'required|email|exists:customers,email'], [
            'email.exists' => 'No encontramos una cuenta con ese correo electrónico.'
        ]);

        try {
            // 1. Generar código de 6 dígitos
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // 2. Persistir en la tabla del Silo Customer
            DB::table('password_reset_codes_customers')->updateOrInsert(
                ['email' => $request->email],
                [
                    'token' => $code, 
                    'created_at' => Carbon::now()
                ]
            );

            // 3. Enviar Correo
            Mail::to($request->email)->send(new CustomerResetCodeMail($code));
            Log::info("Código {$code} enviado exitosamente a: " . $request->email);

            // 4. REDIRECCIÓN FÍSICA AL PASO 2 (ResetPassword)
            return redirect()->route('password.reset', ['email' => $request->email])
                ->with('status', 'Código enviado. Revisa tu bandeja de entrada.');

        } catch (\Exception $e) {
            Log::error('ERROR EN PASO 1 RECUPERACIÓN:', ['error' => $e->getMessage()]);
            return back()->withErrors(['email' => 'Error al procesar la solicitud.']);
        }
    }
}