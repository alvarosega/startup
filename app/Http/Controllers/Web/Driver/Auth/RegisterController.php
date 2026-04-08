<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Driver\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\Auth\RegisterRequest;
use App\DTOs\Driver\Auth\RegisterDriverData;
use App\Http\Requests\Driver\Auth\ValidateStep1Request;
use App\Actions\Driver\Auth\RegisterDriverAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class RegisterController extends Controller
{
    public function create(): Response { return Inertia::render('Driver/Auth/Register'); }

    public function validateStep1(ValidateStep1Request $request): RedirectResponse { return back(); }

    public function store(RegisterRequest $request, RegisterDriverAction $action): RedirectResponse
    {
        try {
            $data = RegisterDriverData::fromRequest($request);
            $driver = $action->execute($data);

            // AUTO-LOGIN INMEDIATO
            Auth::guard('driver')->login($driver);

            // CRÍTICO: Lo redirigimos directamente a SU PERFIL, no al dashboard
            // En su perfil verá que está en estado 'pending'
            return redirect()->route('driver.profile.index')
                ->with('success', 'Registro exitoso. Debes esperar la validación del administrador.');
            
        } catch (\Exception $e) {
            Log::error('[DriverRegister] Fallo: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Fallo operativo al procesar el registro.'])->withInput();
        }
    }
}