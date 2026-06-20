<?php

namespace App\Http\Controllers\Web\Driver\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\Auth\ResetPasswordRequest;
use App\Actions\Driver\Auth\ResetPasswordAction;
use App\DTOs\Driver\Auth\ResetPasswordData;
use Inertia\Inertia;

class ResetPasswordController extends Controller
{
    public function showResetForm($email)
    {
        return Inertia::render('Driver/Auth/ResetPassword', ['email' => $email]);
    }

    public function reset(ResetPasswordRequest $request, ResetPasswordAction $action)
    {
        $data = ResetPasswordData::fromRequest($request);
        
        $action->execute($data);

        return redirect()->route('driver.login')
            ->with('message', 'Contraseña actualizada correctamente.');
    }
}