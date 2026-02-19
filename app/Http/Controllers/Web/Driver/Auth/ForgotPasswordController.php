<?php

namespace App\Http\Controllers\Web\Driver\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\Auth\ForgotPasswordRequest;
use App\Actions\Driver\Auth\SendResetCodeAction;
use Inertia\Inertia;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm() { return Inertia::render('Driver/Auth/ForgotPassword'); }

    public function sendResetCode(ForgotPasswordRequest $request, SendResetCodeAction $action)
    {
        try {
            $action->execute($request->validated('email'));
            return redirect()->route('driver.password.reset', ['email' => $request->email])
                ->with('status', 'Código de seguridad enviado.');
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Error al enviar el código.']);
        }
    }
}