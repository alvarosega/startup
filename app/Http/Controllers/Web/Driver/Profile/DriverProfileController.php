<?php

namespace App\Http\Controllers\Web\Driver\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DriverProfileController extends Controller
{
    public function index()
    {
        $driver = Auth::guard('driver')->user();
        $driver->load(['profile', 'branch']);

        return Inertia::render('Driver/Profile/Index', [
            'driver' => $driver,
            'status' => $driver->status,
            // Calculamos si tiene los documentos básicos subidos
            'hasDocs' => (bool)($driver->profile?->ci_front_path && $driver->profile?->license_photo_path)
        ]);
    }

    public function uploadDocs(Request $request)
    {
        $request->validate([
            'ci_front'      => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'license_photo' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        $driver = Auth::guard('driver')->user();
        $profile = $driver->profile;

        // Guardamos y actualizamos rutas
        if ($request->hasFile('ci_front')) {
            if ($profile->ci_front_path) Storage::disk('public')->delete($profile->ci_front_path);
            $profile->ci_front_path = $request->file('ci_front')->store('drivers/documents', 'public');
        }

        if ($request->hasFile('license_photo')) {
            if ($profile->license_photo_path) Storage::disk('public')->delete($profile->license_photo_path);
            $profile->license_photo_path = $request->file('license_photo')->store('drivers/documents', 'public');
        }

        $profile->save();

        // Opcional: Podrías cambiar el status del driver a 'reviewing' si lo deseas
        // $driver->update(['status' => 'pending']); 

        return back()->with('success', 'Documentos recibidos.');
    }

    public function update(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'vehicle_type' => ['required', 'string', 'in:moto,car,truck'],
        ]);

        Auth::guard('driver')->user()->profile()->update($request->only([
            'first_name', 'last_name', 'vehicle_type'
        ]));

        return back()->with('success', 'Información actualizada.');
    }
}