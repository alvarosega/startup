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
use App\Actions\Customer\Cart\SyncGuestCartAction; // INYECCIÓN

class RegisterController extends Controller
{
    public function create(GetActiveBranchesAction $branchAction): Response
    {
        $avatars = collect(range(1, 8))->map(function ($i) {
            return [
                'id' => "avatar_{$i}.png",
                'url' => asset("assets/avatars/avatar_{$i}.png")
            ];
        })->toArray();

        return Inertia::render('Customer/Auth/Register', [
            'activeBranches' => BranchResource::collection($branchAction->execute())->resolve(),
            'availableAvatars' => $avatars
        ]);
    }

    public function validateStep1(ValidateStep1Request $request)
    {
        return response()->json(['valid' => true], 200);
    }

    public function store(RegisterRequest $request, RegisterCustomerAction $action, SyncGuestCartAction $syncCartAction): RedirectResponse
    {
        // Captura preventiva del UUID de invitado antes de desmantelar la sesión vieja
        $guestUuid = $request->session()->get('guest_client_uuid');

        try {
            $data = RegisterCustomerData::fromRequest($request);
            
            $customer = $action->execute(
                $data, 
                $request->header('X-Idempotency-Key')
            ); 

            Auth::guard('customer')->login($customer);

            $request->session()->regenerate();
            
            // Invocación explícita delegando la limpieza del guest_client_uuid al Action
            $syncCartAction->execute((string) $customer->id, $guestUuid);

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