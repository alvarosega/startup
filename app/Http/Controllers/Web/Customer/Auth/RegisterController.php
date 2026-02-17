<?php

namespace App\Http\Controllers\Web\Customer\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Models\Branch;
use App\Http\Requests\Customer\Auth\RegisterRequest;
use App\DTOs\Customer\Auth\RegisterCustomerData;
use App\Actions\Customer\Auth\RegisterCustomerAction;
use App\Traits\ValidatesGlobalIdentity;

class RegisterController extends Controller
{
    use ValidatesGlobalIdentity;

    public function create()
    {
        $activeBranches = Branch::where('is_active', true)
            ->get()
            ->map(fn($b) => [
                'id' => bin2hex($b->getRawOriginal('id')),
                'name' => $b->name,
                'latitude' => (float) $b->latitude,
                'longitude' => (float) $b->longitude,
            ]);

        return Inertia::render('Customer/Auth/Register', ['activeBranches' => $activeBranches]);
    }

    public function validateStep1(Request $request)
    {
        
        $request->validate([
            'phone'    => $this->globalPhoneRules(),
            'email'    => $this->globalEmailRules(),
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
            'terms'    => ['accepted'],
        ]);

        return response()->json(['status' => 'success']);
    }

    public function store(RegisterRequest $request, RegisterCustomerAction $action)
    {
        try {
            $data = RegisterCustomerData::fromRequest($request);
            $customer = $action->execute($data);

            Auth::guard('customer')->login($customer);
            $request->session()->regenerate();

            Log::info('[CustomerRegister] Éxito', ['id' => bin2hex($customer->getRawOriginal('id'))]);

            return redirect()->intended('/');
        } catch (\Exception $e) {
            Log::error('[CustomerRegister] Error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error del sistema: ' . $e->getMessage()]);
        }
    }
}