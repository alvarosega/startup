<?php

namespace App\Http\Controllers\Web\Driver\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\Auth\ResetPasswordRequest;
use App\Actions\Driver\Auth\ResetPasswordAction;
use Inertia\Inertia;

class ResetPasswordController extends Controller
{
    public function showResetForm($email)
    {
        return Inertia::render('Driver/Auth/ResetPassword', ['email' => $email]);
    }

    public function reset(ResetPasswordRequest $request, ResetPasswordAction $action)
    {
        try {
            $action->execute(
                $request->validated('email'),
                $request->validated('code'),
                $request->validated('password')
            );

            return redirect()->route('driver.login')
                ->with('message', 'Contraseña actualizada correctamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }
}