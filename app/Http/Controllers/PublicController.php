<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
// Importamos los modelos para que el catálogo funcione
use App\Models\Product;
use App\Models\Promotion;

class PublicController extends Controller
{
    /**
     * Pantalla Principal Pública (Catálogo y Promociones)
     */
    public function index()
    {
        return Inertia::render('Public/Home', [
            //'products' => Product::where('is_active', true)->get(),
            //'promotions' => Promotion::where('is_active', true)->get(),
        ]);
    }
}