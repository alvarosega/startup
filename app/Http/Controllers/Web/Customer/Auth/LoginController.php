<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Customer\Auth;

use App\Actions\Customer\Auth\LoginCustomerAction;
use App\DTOs\Customer\Auth\LoginCustomerData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class LoginController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Customer/Auth/Login', [
            'status' => session('status'),
        ]);
    }

    public function store(LoginRequest $request, LoginCustomerAction $action): RedirectResponse
    {
        $request->checkRateLimit();

        $validated = $request->validated();
        $validated['guest_client_uuid'] = $request->session()->get('guest_client_uuid');

        $dto = LoginCustomerData::fromArray($validated);
        $result = $action->execute($dto);

        if (!$result->isSuccess) {
            $request->hitRateLimiter();
            return back()->withErrors(['phone' => $result->errorMessage])->withInput();
        }

        $request->clearRateLimiter();
        $request->session()->regenerate();

        return redirect()->intended(route('customer.index'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        \Illuminate\Support\Facades\Auth::guard('customer')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('customer.index');
    }
}