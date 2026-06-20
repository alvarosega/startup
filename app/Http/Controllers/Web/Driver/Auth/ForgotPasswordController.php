<?php

namespace App\Http\Controllers\Web\Driver\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\Auth\ForgotPasswordRequest;
use App\Actions\Driver\Auth\SendResetCodeAction;
use App\DTOs\Driver\Auth\SendResetCodeData;
use Inertia\Inertia;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm() 
    { 
        return Inertia::render('Driver/Auth/ForgotPassword'); 
    }

    public function sendResetCode(ForgotPasswordRequest $request, SendResetCodeAction $action)
    {
        $data = SendResetCodeData::fromRequest($request);
        
        $action->execute($data);

        return redirect()->route('driver.password.reset', ['email' => $data->email])
            ->with('status', 'Código de seguridad enviado.');
    }
}