<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Branch;
use App\Models\Sku;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\DTOs\Price\PriceData;
use App\Http\Requests\Price\UpdatePriceRequest; // Recuerda crear este Request con las nuevas reglas
use App\Actions\Price\UpdateBranchPrice;

class PriceController extends Controller
{
    public function index(Request $request)
    {
        $branches = Branch::where('is_active', true)->orderBy('id')->get(['id', 'name']);

        // 1. Buscamos PRODUCTOS (Maestros), no inventario.
        // Queremos ver todo el catálogo para asignarle precios.
        $query = Product::with([
            'brand:id,name',
            'category:id,name',
            // Cargamos SKUs y sus precios ACTIVOS
            'skus' => function($q) {
                $q->with(['prices' => function($p) {
                    // Solo traemos precios vigentes para no llenar la memoria de basura
                    $p->where('valid_from', '<=', now())
                      ->where(function($sub) {
                          $sub->whereNull('valid_to')->orWhere('valid_to', '>=', now());
                      })
                      ->orderBy('branch_id')
                      ->orderBy('type'); // Ordenar: Nacional -> Sucursal, Regular -> Oferta
                }]);
            }
        ]);

        // Filtro Básico
        if ($request->search) {
            $term = $request->search;
            $query->where(function($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhereHas('skus', fn($s) => $s->where('code', 'like', "%{$term}%"));
            });
        }

        // Paginamos por PRODUCTO, no por SKU, para mantener el agrupamiento visual
        $products = $query->orderBy('name')->paginate(10)->withQueryString();

        // Transformación para facilitar la "Matriz" en el Frontend
        // No usamos Resource aquí para tener flexibilidad en la estructura de matriz
        $matrix = $products->through(function ($product) use ($branches) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'brand' => $product->brand?->name,
                'image_url' => $product->image_url,
                'skus' => $product->skus->map(function ($sku) use ($branches) {
                    
                    // Mapeamos los precios existentes a un objeto fácil de leer por ID de sucursal
                    // Ej: { 'null': PrecioNacional, '1': PrecioSucursal1, '2': PrecioSucursal2 }
                    $pricesByBranch = $sku->prices->groupBy(fn($p) => $p->branch_id ?? 'national');

                    return [
                        'id' => $sku->id,
                        'name' => $sku->name,
                        'code' => $sku->code,
                        'base_price' => $sku->base_price, // Precio de referencia
                        
                        // Esto permite al frontend decir: "Si prices['2'] existe, muestra el precio, si no, botón 'Agregar'"
                        'prices_matrix' => $pricesByBranch, 
                    ];
                }),
            ];
        });

        return Inertia::render('Admin/Prices/Index', [
            'products' => $matrix, // La estructura lista para iterar
            'branches' => $branches, // Las columnas de la tabla
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(UpdatePriceRequest $request, UpdateBranchPrice $action)
    {
        $data = PriceData::fromRequest($request);
        $action->execute($data);
        return back()->with('success', 'Precio configurado correctamente.');
    }
}