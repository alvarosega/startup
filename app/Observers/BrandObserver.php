<?php

namespace App\Observers;

use App\Models\Brand;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BrandObserver
{
    public function creating(Brand $brand): void
    {
        if (empty($brand->slug)) {
            $brand->slug = Str::slug($brand->name);
        }
    }

    public function updated(Brand $brand): void
    {
        if ($brand->isDirty('image_path')) {
            $original = $brand->getOriginal('image_path');
            if ($original && Storage::disk('public')->exists($original)) {
                Storage::disk('public')->delete($original);
            }
        }
    }

    public function forceDeleted(Brand $brand): void
    {
        if ($brand->image_path && Storage::disk('public')->exists($brand->image_path)) {
            Storage::disk('public')->delete($brand->image_path);
        }
    }
}