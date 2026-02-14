<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sku;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Sku\UpsertSkuRequest; // Necesitarás crear este Request
use App\DTOs\Sku\SkuDTO; // Necesitarás crear este DTO

class SkuController extends Controller
{
    public function store(Request $request)
    {
        // Validación básica (mejor usar Form Request)
        $validated = $request->validate([
            'product_id' => 'required|uuid|exists:products,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:skus,code',
            'conversion_factor' => 'required|numeric|min:0.001|max:1000',
            'weight' => 'nullable|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        try {
            DB::transaction(function () use ($validated, $request) {
                // Crear SKU
                $sku = Sku::create([
                    'product_id' => $validated['product_id'],
                    'name' => $validated['name'],
                    'code' => $validated['code'] ?? null,
                    'base_price' => $validated['price'],
                    'conversion_factor' => $validated['conversion_factor'],
                    'weight' => $validated['weight'] ?? 0,
                    'image_path' => $request->hasFile('image') 
                        ? $request->file('image')->store('skus', 'public')
                        : null,
                    'is_active' => true,
                ]);

                // Crear precio base (nacional)
                $sku->prices()->create([
                    'branch_id' => null,
                    'list_price' => $validated['price'] * 1.10,
                    'final_price' => $validated['price'],
                    'min_quantity' => 1,
                    'valid_from' => now(),
                ]);
            });

            return redirect()->back()->with([
                'success' => 'SKU creado exitosamente.',
                'refresh' => true, // Para que Inertia recargue
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'error' => 'Error al crear SKU: ' . $e->getMessage()
            ]);
        }
    }

    public function update(Request $request, Sku $sku)
    {
        // Validación
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:skus,code,' . $sku->id,
            'conversion_factor' => 'required|numeric|min:0.001|max:1000',
            'weight' => 'nullable|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        try {
            DB::transaction(function () use ($validated, $request, $sku) {
                // Manejar imagen
                $imagePath = $sku->image_path;
                if ($request->hasFile('image')) {
                    // Eliminar imagen anterior si existe
                    if ($sku->image_path) {
                        Storage::disk('public')->delete($sku->image_path);
                    }
                    $imagePath = $request->file('image')->store('skus', 'public');
                }

                // Actualizar SKU
                $sku->update([
                    'name' => $validated['name'],
                    'code' => $validated['code'] ?? null,
                    'base_price' => $validated['price'],
                    'conversion_factor' => $validated['conversion_factor'],
                    'weight' => $validated['weight'] ?? 0,
                    'image_path' => $imagePath,
                ]);

                // Actualizar precio si cambió
                $currentPrice = $sku->prices()
                    ->whereNull('branch_id')
                    ->latest()
                    ->first();

                if (!$currentPrice || (float)$currentPrice->final_price !== (float)$validated['price']) {
                    if ($currentPrice) {
                        $currentPrice->delete(); // Soft delete del precio anterior
                    }

                    $sku->prices()->create([
                        'branch_id' => null,
                        'list_price' => $validated['price'] * 1.10,
                        'final_price' => $validated['price'],
                        'min_quantity' => 1,
                        'valid_from' => now(),
                    ]);
                }
            });

            return redirect()->back()->with([
                'success' => 'SKU actualizado exitosamente.',
                'refresh' => true,
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'error' => 'Error al actualizar SKU: ' . $e->getMessage()
            ]);
        }
    }

    public function destroy(Sku $sku)
    {
        try {
            // Verificar si el SKU tiene inventario o pedidos antes de eliminar
            $hasInventory = $sku->inventoryLots()->exists();
            // $hasOrders = $sku->orderItems()->exists(); // Si tienes relación con órdenes

            if ($hasInventory) {
                return redirect()->back()->withErrors([
                    'error' => 'No se puede eliminar el SKU porque tiene inventario asociado.'
                ]);
            }

            // Soft delete del SKU
            $sku->delete();

            return redirect()->back()->with([
                'success' => 'SKU eliminado exitosamente.',
                'refresh' => true,
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'error' => 'Error al eliminar SKU: ' . $e->getMessage()
            ]);
        }
    }

    // Método adicional útil: Activar/Desactivar SKU
    public function toggleStatus(Sku $sku)
    {
        try {
            $sku->update([
                'is_active' => !$sku->is_active
            ]);

            $status = $sku->is_active ? 'activado' : 'desactivado';
            
            return redirect()->back()->with([
                'success' => "SKU {$status} exitosamente.",
                'refresh' => true,
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'error' => 'Error al cambiar estado: ' . $e->getMessage()
            ]);
        }
    }

    // Método para clonar SKU
    public function duplicate(Sku $sku)
    {
        try {
            DB::transaction(function () use ($sku) {
                $newSku = $sku->replicate();
                $newSku->code = $sku->code . '-COPY';
                $newSku->base_price = $sku->base_price;
                $newSku->save();

                // Clonar precio si existe
                $currentPrice = $sku->prices()
                    ->whereNull('branch_id')
                    ->latest()
                    ->first();

                if ($currentPrice) {
                    $newSku->prices()->create([
                        'branch_id' => null,
                        'list_price' => $currentPrice->list_price,
                        'final_price' => $currentPrice->final_price,
                        'min_quantity' => $currentPrice->min_quantity,
                        'valid_from' => now(),
                    ]);
                }
            });

            return redirect()->back()->with([
                'success' => 'SKU duplicado exitosamente.',
                'refresh' => true,
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'error' => 'Error al duplicar SKU: ' . $e->getMessage()
            ]);
        }
    }
}