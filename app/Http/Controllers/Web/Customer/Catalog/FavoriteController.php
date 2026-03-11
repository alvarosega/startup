<?php

namespace App\Http\Controllers\Web\Customer\Catalog;

use App\Http\Controllers\Controller;
use App\DTOs\Customer\Catalog\ToggleFavoriteDTO;
use App\Actions\Customer\Catalog\ToggleFavoriteAction;

class FavoriteController extends Controller
{
    public function toggle(string $productId, ToggleFavoriteAction $action)
    {
        $dto = ToggleFavoriteDTO::fromRequest($productId);
        
        try {
            $action->execute($dto);
            // Inertia recargará los props automáticamente (preservando el scroll)
            return back(); 
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'No se pudo actualizar favoritos']);
        }
    }
}