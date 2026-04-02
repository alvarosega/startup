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

        // Paleta de colores Cyber para el Glow System
        $cyberColors = [
            '#A855F7', // Purple
            '#EC4899', // Pink
            '#3B82F6', // Blue
            '#10B981', // Green
            '#F59E0B', // Amber
            '#EF4444', // Red
            '#06B6D4', // Cyan
            '#8B5CF6', // Violet
            '#F97316', // Orange
        ];

        $file = fopen(database_path('data/brands.csv'), 'r');
        // Limpieza de BOM y normalización de headers
        $headers = array_map(fn($h) => strtolower(trim(preg_replace('/^[\xef\xbb\xbf]+/', '', $h))), fgetcsv($file, 0, ';'));

        DB::transaction(function () use ($file, $headers, $providers, $categories, $zones, $cyberColors) {
            $rows = [];
            while (($data = fgetcsv($file, 0, ';')) !== false) {
                $rows[] = array_combine($headers, $data);
            }

            foreach ($rows as $row) {
                $providerId = $providers[strtolower(trim($row['provider_ref']))] ?? null;
                $categoryId = $categories[$row['category_slug']] ?? null;

                if (!$providerId || !$categoryId) continue;

                // Seleccionamos un color aleatorio de la paleta
                $randomColor = $cyberColors[array_rand($cyberColors)];

                $brand = Brand::updateOrCreate(['slug' => $row['slug']], [
                    'name'        => $row['name'],
                    'provider_id' => $providerId,
                    'category_id' => $categoryId,
                    'bg_color'    => $randomColor, // <--- INYECCIÓN DE COLOR
                    'is_active'   => true,
                    'is_featured' => filter_var($row['is_featured'] ?? false, FILTER_VALIDATE_BOOLEAN), 
                    'sort_order'  => (int)($row['sort_order'] ?? 0),
                ]);

                if (!empty($row['market_zone_slugs'])) {
                    $slugs = explode(',', $row['market_zone_slugs']);
                    $ids = array_filter(array_map(fn($s) => $zones[trim($s)] ?? null, $slugs));
                    $brand->marketZones()->sync($ids);
                }
            }

            // Segunda Pasada: Jerarquía
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