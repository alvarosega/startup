<?php

namespace App\Http\Controllers\Web\Customer\Profiles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Profiles\AddressRequest;
use App\Actions\Customer\Profiles\UpsertAddressAction;
use App\Actions\Customer\Profiles\SetDefaultAddressAction;
use App\DTOs\Customer\Profiles\AddressData;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AddressController extends Controller
{
    public function index()
    {
        $user = Auth::guard('customer')->user();
        
        return Inertia::render('Customer/Profiles/AddressesPage', [
            'addresses' => $user->addresses()->get(),
            'activeBranches' => \App\Models\Branch::where('is_active', true)->get()
        ]);
    }
    public function store(AddressRequest $request, UpsertAddressAction $action)
    {
        $action->execute(Auth::guard('customer')->user(), AddressData::fromRequest($request));
        return back()->with('success', 'Ubicación guardada.');
    }

    public function update(AddressRequest $request, $id, UpsertAddressAction $action)
    {
        $action->execute(Auth::guard('customer')->user(), AddressData::fromRequest($request), $id);
        return back()->with('success', 'Ubicación actualizada.');
    }

    public function makeDefault($id, SetDefaultAddressAction $action)
    {
        $action->execute(Auth::guard('customer')->user(), $id);
        return back()->with('success', 'Ubicación principal actualizada.');
    }

    public function destroy($id)
    {
        $user = Auth::guard('customer')->user();
        $address = $user->addresses()->findOrFail($id);
        
        if ($address->is_default) {
            $newDefault = $user->addresses()->where('id', '!=', $id)->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
                $user->update(['branch_id' => $newDefault->branch_id]);
            }
        }

        $address->delete();
        return back()->with('success', 'Ubicación eliminada.');
    }
}