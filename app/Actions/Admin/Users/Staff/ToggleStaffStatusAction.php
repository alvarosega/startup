<?php

declare(strict_types=1);

namespace App\Actions\Admin\Users\Staff;

use App\Models\Users\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

final class ToggleStaffStatusAction
{
    public function execute(Admin $admin): bool
    {
        /** @var Admin|null $currentAdmin */
        $currentAdmin = Auth::guard('super_admin')->user();

        if ($currentAdmin && $currentAdmin->id === $admin->id) {
            throw ValidationException::withMessages([
                'error' => 'No puedes suspender tu propia cuenta de administrador.',
            ]);
        }

        $newStatus = !$admin->is_active;
        $admin->update(['is_active' => $newStatus]);

        return $newStatus;
    }
}
