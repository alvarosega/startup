<?php

namespace App\Http\Controllers\Web\Driver\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\Auth\RegisterRequest;
use App\DTOs\Driver\Auth\RegisterDriverData;
use App\Http\Requests\Driver\Auth\ValidateStep1Request;
use App\Actions\Driver\Auth\RegisterDriverAction;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class RegisterController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Driver/Auth/Register');
    }

    public function validateStep1(ValidateStep1Request $request): RedirectResponse
    {
        // Solo valida, si llega aquí es que el teléfono/email están libres
        return back(); 
    }

    public function store(RegisterRequest $request, RegisterDriverAction $action): RedirectResponse
    {
        try {
            $data = RegisterDriverData::fromRequest($request);
            $action->execute($data);

            // RECTIFICACIÓN: No logueamos al usuario. 
            // Redirigimos a una página de éxito/pendiente pública.
            return redirect()->route('driver.register.pending');
            
        } catch (\Exception $e) {
            Log::error('[DriverRegister] Fallo: ' . $e->getMessage());
            return back()->withErrors(['phone' => 'Error al procesar la solicitud.'])->withInput();
        }
    }

    public function pending(): Response
    {
        return Inertia::render('Driver/Auth/AccountPending');
    }
}