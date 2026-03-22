<?php

namespace Database\Seeders;

use App\Models\{Brand, Provider, Category, MarketZone};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $providers = Provider::pluck('id', 'commercial_name')->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id])->toArray();
        $categories = Category::pluck('id', 'slug')->toArray();
        $zones = MarketZone::pluck('id', 'slug')->toArray();

        $file = fopen(database_path('data/brands.csv'), 'r');
        $headers = array_map(fn($h) => strtolower(trim(preg_replace('/^[\xef\xbb\xbf]+/', '', $h))), fgetcsv($file, 0, ';'));

        DB::transaction(function () use ($file, $headers, $providers, $categories, $zones) {
            $rows = [];
            while (($data = fgetcsv($file, 0, ';')) !== false) {
                $rows[] = array_combine($headers, $data);
            }

            foreach ($rows as $row) {
                $providerId = $providers[strtolower(trim($row['provider_ref']))] ?? null;
                $categoryId = $categories[$row['category_slug']] ?? null;

                if (!$providerId || !$categoryId) continue;

                $brand = Brand::updateOrCreate(['slug' => $row['slug']], [
                    'name'        => $row['name'],
                    'provider_id' => $providerId,
                    'category_id' => $categoryId,
                    'is_active'   => (bool)($row['is_active'] ?? 1),
                    'is_featured' => (bool)($row['is_featured'] ?? 0), // Fallback a false si falta en CSV
                    'sort_order'  => (int)($row['sort_order'] ?? 0),
                ]);
                // Sincronización Estratégica MarketZones
                if (!empty($row['market_zone_slugs'])) {
                    $slugs = explode(',', $row['market_zone_slugs']);
                    $ids = array_filter(array_map(fn($s) => $zones[trim($s)] ?? null, $slugs));
                    $brand->marketZones()->sync($ids);
                }
            }

            // Segunda Pasada: Jerarquía de Sub-marcas
            foreach ($rows as $row) {
                if (!empty($row['parent_brand_slug'])) {
                    $parent = Brand::where('slug', $row['parent_brand_slug'])->first();
                    Brand::where('slug', $row['slug'])->update(['parent_id' => $parent?->id]);
                }
            }
        });
        fclose($file);
    }
}