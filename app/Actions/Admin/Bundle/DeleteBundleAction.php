<?php

namespace App\Actions\Admin\Bundle;

use App\Models\Bundle;
use Illuminate\Support\Facades\Storage;

class DeleteBundleAction
{
    public function execute(Bundle $bundle): void
    {
        // Si el sistema usara Hard Delete, borraríamos el archivo aquí.
        // Al ser SoftDelete, mantenemos el archivo pero el Action queda listo
        // para lógica de limpieza si se decide purgar.
        $bundle->delete();
    }
}