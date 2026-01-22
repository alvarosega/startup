<?php

namespace App\Modules\Identity\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Mail\ResetCodeMail;
use Inertia\Inertia;

class PasswordResetController extends Controller
{
    // Vista para solicitar el código (Paso 1)
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
        // Usamos updateOrInsert para no llenar la tabla de basura
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($code), // Hasheamos el código por seguridad
                'created_at' => Carbon::now()
            ]
        );

        // 3. Enviar Email (Usando una clase Mailable simple)
        // Por ahora simulamos el envío en Log si no tienes el Mailable creado
        // Mail::to($email)->send(new ResetCodeMail($code));
        try {
            Mail::to($email)->send(new ResetCodeMail($code));
            Log::info("Correo enviado a $email");
        } catch (\Exception $e) {
            Log::error("Error enviando correo: " . $e->getMessage());
            return back()->withErrors(['email' => 'Error técnico al enviar el correo. Revisa los logs.']);
        }
    }

    // Vista para ingresar código y nueva password (Paso 2)
    // Recibimos el email por query param para pre-llenar el campo
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
            return back()->withErrors(['code' => 'El código es incorrecto o ha expirado.']);
        }

        // Verificar expiración (ej: 15 minutos)
        if (Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
            return back()->withErrors(['code' => 'El código ha expirado. Solicita uno nuevo.']);
        }

        // 3. Actualizar Usuario
        $user = User::where('email', $request->email)->first();
        
        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ]);

        // --- LÓGICA DE VERIFICACIÓN IMPLÍCITA ---
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            // Evento opcional si usas listeners
            // event(new \Illuminate\Auth\Events\Verified($user)); 
        }

        $user->save();

        // 4. Limpiar token usado
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', 'Contraseña restablecida. Tu correo ha sido verificado.');
    }
}