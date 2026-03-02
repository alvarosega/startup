<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('admin.logistics', function ($user) {
    // Verificamos si el usuario es un administrador
    // Si usas múltiples guards, Laravel inyecta el usuario autenticado automáticamente
    return Auth::guard('super_admin')->check();
});