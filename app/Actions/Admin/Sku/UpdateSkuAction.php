<?php


namespace App\Actions\Admin\Sku;

use App\Models\Sku;
use Illuminate\Support\Facades\{DB, Storage};

class UpdateSkuAction
{
    public function execute(Sku $sku, array $data): void
    {
        DB::transaction(function () use ($sku, $data) {
            $oldPrice = (float) $sku->base_price;
            $newPrice = (float) $data['base_price'];

            // 1. Actualización de Imagen
            if (isset($data['image'])) {
                if ($sku->image_path) Storage::disk('public')->delete($sku->image_path);
                $data['image_path'] = $data['image']->store('skus', 'public');
            }

            // 2. Registro en Historial de Precios si hay cambio
            if ($oldPrice !== $newPrice) {
                $sku->prices()->create([
                    'type'        => 'regular',
                    'list_price'  => $newPrice,
                    'final_price' => $newPrice,
                    'valid_from'  => now()
                ]);
            }

            $sku->update($data);
        });
    }
}