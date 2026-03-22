<?php

namespace App\Http\Controllers\Web\Customer\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Models\Branch;
use App\Http\Requests\Customer\Auth\RegisterRequest;
use App\Http\Requests\Customer\Auth\ValidateStep1Request;
use App\DTOs\Customer\Auth\RegisterCustomerData;
use App\Actions\Customer\Auth\RegisterCustomerAction;
use App\Actions\Customer\Auth\GetActiveBranchesAction; 
use App\Http\Resources\Customer\Branch\BranchResource; 
use App\Exceptions\IdentityCollisionException;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function create(GetActiveBranchesAction $branchAction)
    {
        return Inertia::render('Customer/Auth/Register', [
            // .resolve() extrae el array de la envoltura 'data'
            'activeBranches' => BranchResource::collection($branchAction->execute())->resolve()
        ]);
    }

    public function validateStep1(ValidateStep1Request $request)
    {
        // Solo valida, si llega aquí es exitoso
        return back(); 
    }

    public function store(RegisterRequest $request, RegisterCustomerAction $action)
    {
        try {
            $data = RegisterCustomerData::fromRequest($request);
            
            // El Action ahora recibe la llave de idempotencia del Header
            $action->execute($data, $request->header('X-Idempotency-Key')); 
    
            $request->session()->regenerate();
            return redirect()->route('customer.shop.index');
    
        } catch (IdentityCollisionException $e) { // Excepción específica
            return back()->withErrors(['phone' => $e->getMessage()])->withInput();
        } catch (\Exception $e) {
            Log::emergency('[CustomerRegister] Critical Failure', ['exception' => $e]);
            return back()->withErrors(['phone' => 'Error interno de registro.'])->withInput();
        }
    }
}