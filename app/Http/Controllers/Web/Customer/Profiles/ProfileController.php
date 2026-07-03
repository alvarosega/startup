<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Customer\Profiles;

use App\Actions\Customer\Profiles\UpdatePersonalDataAction;
use App\Actions\Customer\Profiles\UpdateAvatarAction;
use App\DTOs\Customer\Profiles\ProfileUpdateData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Profiles\UpdateProfileRequest;
use App\Http\Resources\Customer\Auth\CustomerResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class ProfileController extends Controller
{
    public function index(): Response
    {
        /** @var \App\Models\Users\Customer $user */
        $user = Auth::guard('customer')->user();
        $user->load('profile');
    
        $availableAvatars = collect(range(1, 8))->map(fn(int $i) => [
            'id' => "avatar_{$i}.png",
            'url' => asset("assets/avatars/avatar_{$i}.png")
        ])->toArray();
    
        return Inertia::render('Customer/Profiles/PersonalInfoPage', [
            'user' => (new CustomerResource($user))->resolve(), 
            'availableAvatars' => $availableAvatars,
            'config' => [
                'identity_locked' => true 
            ]
        ]);
    }

    public function security(): Response
    {
        return Inertia::render('Customer/Profiles/SecurityPage');
    }

    public function update(UpdateProfileRequest $request, UpdatePersonalDataAction $action): RedirectResponse
    {
        $dto = ProfileUpdateData::fromArray($request->validated());
        
        /** @var \App\Models\Users\Customer $user */
        $user = Auth::guard('customer')->user();
        
        $action->execute($user, $dto);
    
        return back()->with('success', 'Perfil actualizado.');
    }

    public function updateAvatar(Request $request, UpdateAvatarAction $action): RedirectResponse
    {
        $request->validate([
            'avatar_source' => ['required', 'string', 'regex:/^avatar_[1-8]\.png$/']
        ]);

        /** @var \App\Models\Users\Customer $user */
        $user = Auth::guard('customer')->user();
        
        $action->execute($user, (string) $request->input('avatar_source'));

        return back()->with('success', 'Avatar actualizado.');
    }
}