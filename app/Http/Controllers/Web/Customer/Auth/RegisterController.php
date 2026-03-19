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

class RegisterController extends Controller
{
    public function create()
    {
        return Inertia::render('Customer/Auth/Register', [
            'activeBranches' => Branch::where('is_active', true)
                ->get(['id', 'name', 'latitude', 'longitude', 'coverage_polygon'])
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
            
            /** * El Action ejecuta la lógica atómica:
             * 1. Resuelve Sucursal (Geo o Default)
             * 2. Crea Customer y Address (NOT NULL branch_id)
             * 3. Login Automático
             * 4. Sync Carrito
             */
            $action->execute($data); 

            $request->session()->regenerate();
            
            return redirect()->route('customer.shop.index');

        } catch (\Exception $e) {
            Log::emergency('[CustomerRegister] Error:', ['msg' => $e->getMessage()]);
            return back()->withErrors(['phone' => 'No pudimos crear tu cuenta. Verifica tus datos.'])
                         ->withInput();
        }
    }
}