<?php

declare(strict_types=1);

namespace App\Actions\Admin\Bundle;

use App\Models\Bundle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class DestroyBundleAction
{
    public function execute(Bundle $bundle): void
    {
        DB::transaction(function () use ($bundle) {
            if ($bundle->image_path) {
                Storage::disk('public')->delete($bundle->image_path);
            }
            
            $bundle->delete();
        });
    }
}