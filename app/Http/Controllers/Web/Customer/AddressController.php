<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\CustomerAddress;

class AddressController extends Controller
{
    /**
     * Helper privado para convertir Hex a Binario de forma segura
     */
    private function safeHexToBin($val)
    {
        if ($val && ctype_xdigit($val) && strlen($val) === 32) {
            return hex2bin($val);
        }
        return $val; // Retornar null o el valor original si no es hex válido
    }

    public function store(Request $request)
    {
        $user = Auth::guard('customer')->user();

        if ($user->addresses()->count() >= 5) {
            return back()->withErrors(['limit' => 'Límite de direcciones alcanzado.']);
        }

        $validated = $request->validate([
            'alias'     => 'required|string|max:50',
            'address'   => 'required|string|max:255',
            'details'   => 'nullable|string|max:255',
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'branch_id' => 'nullable|string|size:32', // Viene como Hex
            'is_default'=> 'boolean'
        ]);

        $isFirst = $user->addresses()->count() === 0;
        $shouldBeDefault = $isFirst || ($validated['is_default'] ?? false);

        if ($shouldBeDefault) {
            $user->addresses()->update(['is_default' => false]);
        }

        // --- CONVERSIÓN MANUAL HEX -> BIN ---
        $binaryBranchId = $this->safeHexToBin($validated['branch_id'] ?? null);

        $address = $user->addresses()->create([
            'alias'      => $validated['alias'],
            'address'    => $validated['address'],
            'reference'  => $validated['details'] ?? null,
            'latitude'   => $validated['latitude'],
            'longitude'  => $validated['longitude'],
            'branch_id'  => $binaryBranchId, // Guardamos Binario
            'is_default' => $shouldBeDefault
        ]);

        // Actualizar usuario si corresponde
        if ($shouldBeDefault && $address->branch_id) {
            // Address->branch_id ya es binario aquí, se puede pasar directo
            $user->branch_id = $address->branch_id; 
            $user->save();
        }

        return back()->with('success', 'Dirección agregada correctamente.');
    }

    public function update(Request $request, $id)
    {
        $user = Auth::guard('customer')->user();
        
        // El interceptor 'newEloquentBuilder' del modelo se encarga de traducir el $id (Hex) a Binario
        $address = $user->addresses()->findOrFail($id);

        $validated = $request->validate([
            'alias'     => 'required|string|max:50',
            'address'   => 'required|string|max:255',
            'details'   => 'nullable|string|max:255',
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'branch_id' => 'nullable|string|size:32',
            'is_default'=> 'boolean'
        ]);

        if ($validated['is_default']) {
            $user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            
            if (!empty($validated['branch_id'])) {
                $user->branch_id = $this->safeHexToBin($validated['branch_id']);
                $user->save();
            }
        }

        // --- CONVERSIÓN MANUAL HEX -> BIN ---
        $binaryBranchId = $this->safeHexToBin($validated['branch_id'] ?? null);

        $address->update([
            'alias'      => $validated['alias'],
            'address'    => $validated['address'],
            'reference'  => $validated['details'] ?? null,
            'latitude'   => $validated['latitude'],
            'longitude'  => $validated['longitude'],
            'branch_id'  => $binaryBranchId, // Guardamos Binario
            'is_default' => $validated['is_default']
        ]);

        return back()->with('success', 'Dirección actualizada.');
    }

    public function destroy($id)
    {
        $user = Auth::guard('customer')->user();
        $address = $user->addresses()->findOrFail($id);

        if ($address->is_default) {
            $nextAddress = $user->addresses()
                ->where('id', '!=', $address->id)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($nextAddress) {
                $nextAddress->update(['is_default' => true]);
                if ($nextAddress->branch_id) {
                    $user->branch_id = $nextAddress->branch_id;
                    $user->save();
                }
            }
        }

        $address->delete();
        return back()->with('success', 'Dirección eliminada.');
    }

    public function makeDefault(CustomerAddress $address)
    {
        $user = Auth::guard('customer')->user();

        // Comparación Binaria vs Binaria (Segura)
        if ($address->customer_id !== $user->id) {
            abort(403, 'No tienes permiso.');
        }

        DB::transaction(function () use ($user, $address) {
            $user->addresses()->update(['is_default' => false]);
            $address->update(['is_default' => true]);
            
            // Sincronizar branch_id del usuario
            if ($address->branch_id) {
                $user->branch_id = $address->branch_id;
                $user->save();
            }
        });

        return back()->with('success', 'Dirección principal actualizada.');
    }
}