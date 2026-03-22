<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $file = fopen(database_path('data/categories.csv'), 'r');
        $headers = array_map(fn($h) => strtolower(trim(preg_replace('/^[\xef\xbb\xbf]+/', '', $h))), fgetcsv($file, 0, ';'));

        DB::transaction(function () use ($file, $headers) {
            $rows = [];
            while (($data = fgetcsv($file, 0, ';')) !== false) {
                $rows[] = array_combine($headers, $data);
            }

            // Pasada 1: Creación de Nodos
            foreach ($rows as $row) {
                Category::updateOrCreate(['slug' => $row['slug']], [
                    'name' => $row['name'],
                    'external_code' => $row['external_code'] ?: null,
                    'tax_classification' => $row['tax_classification'] ?: null,
                    'requires_age_check' => (bool)$row['requires_age_check'],
                    'is_active' => (bool)$row['is_active'],
                    'is_featured' => (bool)$row['is_featured'],
                    'sort_order' => (int)$row['sort_order'],
                    'bg_color' => $row['bg_color'] ?: null,
                ]);
            }

            // Pasada 2: Re-vinculación Jerárquica
            foreach ($rows as $row) {
                if (!empty($row['parent_slug'])) {
                    $parent = Category::where('slug', $row['parent_slug'])->first();
                    Category::where('slug', $row['slug'])->update(['parent_id' => $parent?->id]);
                }
            }
        });
        fclose($file);
    }
}