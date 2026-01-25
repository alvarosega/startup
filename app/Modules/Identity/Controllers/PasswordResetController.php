<?php

namespace App\Modules\Identity\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth; // <--- AGREGAR ESTO
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Mail\ResetCodeMail;
use Inertia\Inertia;

class PasswordResetController extends Controller
{
    // Vista para solicitar el código (Paso 1) - Ya no se usa con el modal, pero se deja por si acaso
    public function showLinkRequestForm()
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    // Enviar el código de 6 dígitos
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        // 1. Generar Código
        $code = rand(100000, 999999);
        $email = $request->email;

        // 2. Guardar en tabla password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($code),
                'created_at' => Carbon::now()
            ]
        );

        // 3. Enviar Email
        try {
            Mail::to($email)->send(new ResetCodeMail($code));
            Log::info("Correo enviado a $email");
            
            // --- CORRECCIÓN PRINCIPAL ---
            // Faltaba este return. Sin él, la petición se queda colgada.
            return back()->with('success', 'Código de verificación enviado a tu correo.');

        } catch (\Exception $e) {
            Log::error("Error enviando correo: " . $e->getMessage());
            return back()->withErrors(['email' => 'Error técnico al enviar el correo. Intenta nuevamente.']);
        }
    }

    // Vista para ingresar código (Paso 2)
    public function showResetForm(Request $request)
    {
        return Inertia::render('Auth/ResetPassword', [
            'email' => $request->query('email'),
        ]);
    }

    // Procesar el cambio de contraseña
    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|numeric|digits:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // 1. Buscar el token en BD
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        // 2. Validaciones de Seguridad
        if (!$record || !Hash::check($request->code, $record->token)) {
            return back()->withErrors(['code' => 'El código es incorrecto.']);
        }

        // Verificar expiración (15 minutos)
        if (Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
            return back()->withErrors(['code' => 'El código ha expirado. Solicita uno nuevo.']);
        }

        // 3. Actualizar Usuario
        $user = User::where('email', $request->email)->first();
        
        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ]);

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        $user->save();

        // 4. Limpiar token usado
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // --- MEJORA DE UX: LOGIN AUTOMÁTICO ---
        // En lugar de enviarlo al login, lo logueamos y lo mandamos al dashboard
        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Contraseña restablecida correctamente.');
    }
}