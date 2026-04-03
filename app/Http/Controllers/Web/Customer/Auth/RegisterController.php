<?php

namespace App\Http\Controllers\Web\Customer\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Requests\Customer\Auth\RegisterRequest;
use App\Http\Requests\Customer\Auth\ValidateStep1Request;
use App\DTOs\Customer\Auth\RegisterCustomerData;
use App\Actions\Customer\Auth\RegisterCustomerAction;
use App\Actions\Customer\Auth\GetActiveBranchesAction; 
use App\Http\Resources\Customer\Branch\BranchResource; 
use App\Exceptions\IdentityCollisionException;

class RegisterController extends Controller
{
    public function create(GetActiveBranchesAction $branchAction): Response
    {
        // AQUÍ SE DEFINE LA CANTIDAD (range 1 a 8) Y LA EXTENSIÓN (.png)
        $avatars = collect(range(1, 8))->map(function ($i) {
            return [
                'id' => "avatar_{$i}.png", // <--- Definición del identificador
                'url' => asset("assets/avatars/avatar_{$i}.png") // <--- Definición de la ruta pública
            ];
        })->toArray();

        return Inertia::render('Customer/Auth/Register', [
            'activeBranches' => BranchResource::collection($branchAction->execute())->resolve(),
            'availableAvatars' => $avatars // Se inyecta al frontend
        ]);
    }

    public function validateStep1(ValidateStep1Request $request)
    {
        // Si el request llega aquí, significa que pasó TODAS las validaciones de ValidateStep1Request
        // (incluyendo el Trait ValidatesGlobalIdentity).
        
        // CORRECCIÓN: Devolver un código 200 OK vacío. 
        // Inertia interpretará esto como un éxito y disparará el onSuccess() en Vue.
        return response()->json(['valid' => true], 200);
    }

    public function store(RegisterRequest $request, RegisterCustomerAction $action): RedirectResponse
    {
        try {
            $data = RegisterCustomerData::fromRequest($request);
            
            // 1. Ejecución Atómica de Persistencia
            $customer = $action->execute(
                $data, 
                $request->header('X-Idempotency-Key')
            ); 

            // 2. Protocolo de Autoridad: Login bajo Guard específico
            Auth::guard('customer')->login($customer);

            // 3. Higiene de Sesión: Previene Session Fixation Attacks
            $request->session()->regenerate();
            
            // 4. Limpieza de rastros de invitado
            $request->session()->forget('guest_client_uuid');

            return redirect()->route('customer.index')
                ->with('status', 'Registro completado con éxito.');
    
        } catch (IdentityCollisionException $e) {
            return back()->withErrors(['phone' => $e->getMessage()])->withInput();
        } catch (\Exception $e) {
            Log::emergency('[CustomerRegister] Critical Failure', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['phone' => 'Error de integridad en el registro.'])->withInput();
        }
    }
}