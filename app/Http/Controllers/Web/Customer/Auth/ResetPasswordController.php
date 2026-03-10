<?php

namespace App\Http\Controllers\Web\Customer\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Auth\ResetPasswordRequest; // <--- NUEVO
use App\DTOs\Customer\Auth\ResetPasswordDTO;               // <--- NUEVO
use App\Actions\Customer\Auth\ResetPasswordAction;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ResetPasswordController extends Controller
{
    public function showResetForm($email)
    {
        return Inertia::render('Customer/Auth/ResetPassword', [
            'email' => $email
        ]);
    }

    public function reset(ResetPasswordRequest $request, ResetPasswordAction $action)
    {
        try {
            // Pasamos un DTO estricto a la acción
            $dto = ResetPasswordDTO::fromRequest($request);
            $action->execute($dto);
    
            return redirect()->route('login')
                ->with('message', 'Contraseña actualizada correctamente.');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('[PasswordUpdate] ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error interno al actualizar la clave.']);
        }
    }
}