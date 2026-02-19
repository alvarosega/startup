<?php

namespace App\Http\Controllers\Web\Driver\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\Auth\LoginRequest;
use App\Actions\Driver\Auth\LoginDriverAction;
use App\DTOs\Driver\Auth\LoginDriverData;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Log};

class LoginController extends Controller
{
    public function show() { return Inertia::render('Driver/Auth/Login'); }

    public function store(LoginRequest $request, LoginDriverAction $action)
    {
        Log::info('[LoginDriver] Intento de login iniciado', ['phone' => $request->phone]);

        $data = LoginDriverData::fromRequest($request);

        try {
            $action->execute($data);
            
            // DENTRO DEL TRY, DESPUÉS DE LA EJECUCIÓN DEL ACTION:
            $user = Auth::guard('driver')->user();

            Log::info('[LoginDriver] Autenticación exitosa', [
                'id' => $user->id, // Acceso simple al UUID String
                'email' => $user->email
            ]);

            $request->session()->regenerate();
            
            $url = route('driver.dashboard');
            Log::info('[LoginDriver] Redirigiendo a Dashboard', ['url' => $url]);

            return redirect()->intended($url);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('[LoginDriver] Fallo de validación/credenciales', ['errors' => $e->errors()]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('[LoginDriver] Error inesperado', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return back()->withErrors(['phone' => 'Error interno del servidor.']);
        }
    }

    public function destroy(Request $request)
    {
        Auth::guard('driver')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('driver.login');
    }
}