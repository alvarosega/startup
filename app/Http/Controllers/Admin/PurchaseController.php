<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\InventoryLot;
use App\Models\InventoryMovement; // Asegúrate de tener este modelo
use App\Models\Branch;
use App\Models\Provider;
use App\Models\Sku;
use App\Http\Requests\Purchase\StorePurchaseRequest;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = Purchase::with(['provider', 'branch', 'user'])
            ->withCount('inventoryLots') // Cuántos items tuvo la compra
            ->orderBy('purchase_date', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/Inventory/Purchases/Index', [
            'purchases' => $purchases
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Inventory/Purchases/Create', [
            'branches' => Branch::where('is_active', true)->get(['id', 'name']),
            'providers' => Provider::where('is_active', true)->orderBy('commercial_name')->get(['id', 'commercial_name']),
            // Enviamos SKUs con datos logísticos para el cálculo frontend (Conversión Pack -> Unidad)
            'skus' => Sku::with('product')->where('is_active', true)->get()->map(function($s) {
                return [
                    'id' => $s->id,
                    'product_id' => $s->product_id, 
                    'full_name' => ($s->product->name ?? 'Unknown') . ' - ' . $s->name,
                    'factor' => (float)$s->conversion_factor,
                    'is_base' => (float)$s->conversion_factor === 1.0 // ¿Es la unidad mínima?
                ];
            })
        ]);
    }

    public function store(StorePurchaseRequest $request)
    {
        $data = $request->validated(); // Datos ya limpios y validados

        try {
            DB::transaction(function () use ($data, $request) {
                
                // 1. Calcular Total (Backend es la autoridad final)
                $total = collect($data['items'])->sum(fn($i) => $i['quantity'] * $i['unit_cost']);

                // 2. Crear Cabecera
                $purchase = Purchase::create([
                    'branch_id' => $data['branch_id'],
                    'provider_id' => $data['provider_id'],
                    'user_id' => $request->user()->id,
                    'document_number' => $data['document_number'],
                    'purchase_date' => $data['purchase_date'],
                    'total_amount' => $total,
                    'notes' => $data['notes'] ?? null,
                    'status' => 'COMPLETED'
                ]);

                // 3. Crear Lotes y Movimientos
                foreach ($data['items'] as $item) {
                    
                    // El Observer generará el lot_code automáticamente aquí
                    $lot = InventoryLot::create([
                        'purchase_id' => $purchase->id,
                        'branch_id' => $data['branch_id'],
                        'sku_id' => $item['sku_id'],
                        'quantity' => $item['quantity'],
                        'initial_quantity' => $item['quantity'],
                        'unit_cost' => $item['unit_cost'],
                        'expiration_date' => $item['expiration_date'] ?? null
                    ]);

                    InventoryMovement::create([
                        'branch_id' => $data['branch_id'],
                        'sku_id' => $item['sku_id'],
                        'inventory_lot_id' => $lot->id,
                        'user_id' => $request->user()->id,
                        'type' => 'purchase',
                        'quantity' => $item['quantity'],
                        'unit_cost' => $item['unit_cost'],
                        'reference' => 'Ingreso #' . $purchase->id
                    ]);
                }
            });

            return redirect()->route('admin.purchases.index')->with('message', 'Compra registrada correctamente.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error en transacción: ' . $e->getMessage()])->withInput();
        }
    }
}