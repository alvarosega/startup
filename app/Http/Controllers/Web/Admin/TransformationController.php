<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StoreTransformationRequest;
use App\Models\InventoryLot;
use App\Models\InventoryMovement;
use App\Models\InventoryTransformation;
use App\Models\Sku;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class TransformationController extends Controller
{

    public function index()
    {
        $transformations = \App\Models\InventoryTransformation::with(['branch', 'user', 'sourceSku.product', 'destinationSku.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/Inventory/Transformations/Index', [
            'transformations' => $transformations
        ]);
    }
    public function create()
    {
        $branches = Branch::where('is_active', true)->get(['id', 'name']);

        // BUSCAMOS TODO LO QUE SE PUEDA "ROMPER" (Factor > 1)
        // Y que tenga stock físico real en alguna sucursal
        $inventory = InventoryLot::join('skus', 'inventory_lots.sku_id', '=', 'skus.id')
            ->join('products', 'skus.product_id', '=', 'products.id')
            ->where('skus.conversion_factor', '>', 1) // <--- ESTO INCLUYE BIPACKS (2), SIXPACKS (6), ETC.
            ->select(
                'inventory_lots.branch_id',
                'inventory_lots.sku_id as id',
                'products.name as product_name',
                'skus.name as sku_name',
                'skus.product_id',
                'skus.conversion_factor as factor', // Ej: 2 (Bipack)
                DB::raw('SUM(inventory_lots.quantity - inventory_lots.reserved_quantity) as stock_real')
            )
            ->groupBy('inventory_lots.branch_id', 'inventory_lots.sku_id', 'products.name', 'skus.name', 'skus.product_id', 'skus.conversion_factor')
            ->having('stock_real', '>', 0) // Solo si hay algo que romper
            ->get();

        // Destinos posibles (Cualquier cosa más pequeña que el origen)
        $allSkus = Sku::with('product')
            ->where('is_active', true)
            ->get(['id', 'product_id', 'name', 'conversion_factor']);

        return Inertia::render('Admin/Inventory/Transformations/Create', [
            'branches' => $branches,
            'inventory_sources' => $inventory,
            'all_skus' => $allSkus
        ]);
    }
    public function store(StoreTransformationRequest $request)
    {
        $data = $request->validated();

        try {
            DB::transaction(function () use ($data, $request) {
                
                // 1. Calcular Factor de Conversión
                $sourceSku = Sku::findOrFail($data['source_sku_id']);
                $destSku = Sku::findOrFail($data['destination_sku_id']);
                
                // Ej: Caja(12) / Botella(1) = 12 unidades resultantes por cada caja
                $ratio = $sourceSku->conversion_factor / $destSku->conversion_factor;
                $quantityToRemove = $data['quantity']; // Cajas a romper
                $quantityToCreate = $quantityToRemove * $ratio; // Botellas a nacer

                // 2. Consumir Stock FIFO (Lo difícil)
                $remainingToRemove = $quantityToRemove;
                $totalCostConsumed = 0;

                // Buscar lotes disponibles del origen
                $sourceLots = InventoryLot::where('sku_id', $data['source_sku_id'])
                    ->where('branch_id', $data['branch_id'])
                    ->whereRaw('(quantity - reserved_quantity) > 0')
                    ->orderBy('expiration_date', 'asc') // FIFO
                    ->get();

                if ($sourceLots->sum('quantity') < $quantityToRemove) {
                    throw new \Exception("Stock insuficiente de {$sourceSku->name} para realizar la transformación.");
                }

                foreach ($sourceLots as $lot) {
                    if ($remainingToRemove <= 0) break;

                    $available = $lot->quantity - $lot->reserved_quantity;
                    $take = min($available, $remainingToRemove);

                    // Restar del Lote
                    $lot->decrement('quantity', $take);
                    
                    // Registrar Movimiento de Salida
                    InventoryMovement::create([
                        'branch_id' => $data['branch_id'],
                        'sku_id' => $data['source_sku_id'],
                        'inventory_lot_id' => $lot->id,
                        'user_id' => $request->user()->id,
                        'type' => 'transform_out', // Salida por transformación
                        'quantity' => -$take,
                        'unit_cost' => $lot->unit_cost,
                        'reference' => 'Transformación a ' . $destSku->name
                    ]);

                    // Acumular costo para heredar
                    $totalCostConsumed += ($take * $lot->unit_cost);
                    $remainingToRemove -= $take;
                }

                // 3. Crear Lote de Destino (Heredando Costo)
                // Costo unitario nuevo = Costo Total Consumido / Cantidad Creada
                $newUnitCost = $totalCostConsumed / $quantityToCreate;

                $newLot = InventoryLot::create([
                    'purchase_id' => $sourceLots->first()->purchase_id, // Vinculamos a la compra original (opcional)
                    'branch_id' => $data['branch_id'],
                    'sku_id' => $data['destination_sku_id'],
                    'lot_code' => 'TRF-' . now()->format('ym') . '-' . Str::upper(Str::random(4)),
                    'quantity' => $quantityToCreate,
                    'initial_quantity' => $quantityToCreate,
                    'unit_cost' => $newUnitCost,
                    'expiration_date' => $sourceLots->first()->expiration_date // Heredamos vencimiento del lote más viejo
                ]);

                // Registrar Movimiento de Entrada
                InventoryMovement::create([
                    'branch_id' => $data['branch_id'],
                    'sku_id' => $data['destination_sku_id'],
                    'inventory_lot_id' => $newLot->id,
                    'user_id' => $request->user()->id,
                    'type' => 'transform_in',
                    'quantity' => $quantityToCreate,
                    'unit_cost' => $newUnitCost,
                    'reference' => 'Transformación desde ' . $sourceSku->name
                ]);

                // 4. Log de Auditoría
                InventoryTransformation::create([
                    'branch_id' => $data['branch_id'],
                    'user_id' => $request->user()->id,
                    'source_sku_id' => $data['source_sku_id'],
                    'quantity_removed' => $quantityToRemove,
                    'destination_sku_id' => $data['destination_sku_id'],
                    'quantity_added' => $quantityToCreate,
                    'notes' => $data['notes']
                ]);
            });

            return redirect()->route('admin.inventory.index')->with('message', 'Transformación realizada con éxito.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error: ' . $e->getMessage()]);
        }
    }
}