<?php

use Illuminate\Support\Facades\Schedule;

// Ejecuta el recolector de órdenes cada minuto sin permitir que dos instancias se solapen (Hostinger safe)
Schedule::command('orders:release-expired')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();