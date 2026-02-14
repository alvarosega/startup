<?php

namespace App\Http\Controllers\Web\Driver\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Mail, Log};
use App\Mail\Driver\DriverResetCodeMail;
use Inertia\Inertia;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return Inertia::render('Driver/Auth/ForgotPassword');
    }

    public function sendResetCode(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:drivers,email'], [
            'email.exists' => 'No existe un conductor registrado con este correo.'
        ]);

        try {
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            DB::table('password_reset_codes_drivers')->updateOrInsert(
                ['email' => $request->email],
                ['token' => $code, 'created_at' => Carbon::now()]
            );

            Mail::to($request->email)->send(new DriverResetCodeMail($code));

            return redirect()->route('driver.password.reset', ['email' => $request->email])
                ->with('status', 'Código de seguridad enviado.');

        } catch (\Exception $e) {
            Log::error('Error recuperación Driver:', ['msj' => $e->getMessage()]);
            return back()->withErrors(['email' => 'Error al enviar el código.']);
        }
    }
}