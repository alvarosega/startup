<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Customer\Profiles;

use App\Actions\Customer\Profiles\UpdateCustomerAvatarAction;
use App\Actions\Customer\Profiles\UpdateCustomerProfileAction;
use App\DTOs\Customer\Profiles\UpdateProfileData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Profiles\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use stdClass;

final class ProfileController extends Controller
{
    public function index(): Response
    {
        /** @var string $customerId */
        $customerId = Auth::guard('customer')->id();

        /** @var stdClass|null $profile */
        $profile = DB::table('customer_profiles')
            ->where('customer_id', $customerId)
            ->where('deleted_epoch', 0)
            ->first();

        return Inertia::render('Customer/Profiles/Index', [
            'profile'          => $this->mapProfilePayload($profile),
            'availableAvatars' => $this->getAvailableAvatars(),
        ]);
    }

    public function update(UpdateProfileRequest $request, UpdateCustomerProfileAction $action): RedirectResponse
    {
        /** @var string $customerId */
        $customerId = Auth::guard('customer')->id();

        $dto = UpdateProfileData::fromArray($request->validated());
        $action->execute($customerId, $dto);

        return redirect()->route('customer.profile.index')
            ->with('success', 'Datos de perfil actualizados correctamente.');
    }

    public function updateAvatar(Request $request, UpdateCustomerAvatarAction $action): RedirectResponse
    {
        /** @var string $customerId */
        $customerId = Auth::guard('customer')->id();
        
        /** @var string $avatarSource */
        $avatarSource = $request->input('avatar_source', '');

        $action->execute($customerId, $avatarSource);

        return redirect()->route('customer.profile.index')
            ->with('success', 'Avatar actualizado correctamente.');
    }

    public function security(): Response
    {
        return Inertia::render('Customer/Profiles/Security');
    }

    /**
     * @return array{first_name: string, last_name: string, full_name: string, birth_date: ?string, gender: string, avatar_source: string, avatar_url: string}|null
     */
    private function mapProfilePayload(?stdClass $profile): ?array
    {
        if ($profile === null) {
            return null;
        }

        $firstName = (string) ($profile->first_name ?? '');
        $lastName = (string) ($profile->last_name ?? '');
        $avatarSource = (string) ($profile->avatar_source ?? 'avatar_1.png');

        return [
            'first_name'    => $firstName,
            'last_name'     => $lastName,
            'full_name'     => mb_strtoupper("{$firstName} {$lastName}"),
            'birth_date'    => isset($profile->birth_date) ? (string) $profile->birth_date : null,
            'gender'        => (string) ($profile->gender ?? 'MALE'),
            'avatar_source' => $avatarSource,
            'avatar_url'    => "/assets/avatars/{$avatarSource}",
        ];
    }

    /**
     * @return array<int, array{id: string, url: string}>
     */
    private function getAvailableAvatars(): array
    {
        return array_map(fn (int $i) => [
            'id'  => "avatar_{$i}.png",
            'url' => "/assets/avatars/avatar_{$i}.png",
        ], range(1, 8));
    }
}