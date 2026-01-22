<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\Cart;
use Illuminate\Support\Facades\Session;

class MergeCartOnLogin
{
    public function handle(Login $event): void
    {
        // 1. Obtenemos la ID de sesión ANTERIOR al login (donde estaba el carrito guest)
        // Laravel guarda esto automáticamente en la sesión flash si se configura bien,
        // pero un truco más robusto es buscar el carrito por la sesión actual antes de que se pierda del todo
        // o asumir que el ID de sesión aún no ha cambiado en este punto exacto del evento?
        
        // NOTA TÉCNICA: El evento Login se dispara DESPUÉS de regenerar la sesión.
        // Por eso, la estrategia correcta es buscar si existe un carrito HUÉRFANO con la sesión anterior.
        // PERO, como Laravel rota la ID, lo mejor es actualizar el carrito *antes* de regenerar la sesión
        // en el controlador de Login.
        
        // SIN EMBARGO, para simplificar sin tocar el núcleo de Auth de Laravel/Breeze:
        // Vamos a asumir que en el CartController usaremos 'session()->getId()' actual.
        // Si usas Breeze/Inertia, el método store del AuthenticatedSessionController es el lugar ideal.
    }
}