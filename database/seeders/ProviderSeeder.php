<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Provider;
use Illuminate\Support\Facades\DB;

class ProviderSeeder extends Seeder {
    public function run(): void {
        $filePath = database_path('data/providers.csv');
        if (!file_exists($filePath)) return;

        $file = fopen($filePath, 'r');
        $rawHeaders = fgetcsv($file, 0, ';');
        $headers = array_map(fn($h) => strtolower(preg_replace('/^[\xef\xbb\xbf]+/', '', trim((string)$h))), $rawHeaders);

        DB::transaction(function () use ($file, $headers) {
            while (($data = fgetcsv($file, 0, ';')) !== false) {
                if (count($headers) !== count($data)) continue;
                $row = array_combine($headers, $data);

                $cleanRow = array_map(function($value) {
                    $str = trim((string)$value);
                    if ($str === '') return null;
                    
                    $encoding = mb_detect_encoding($str, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
                    if ($encoding !== 'UTF-8') {
                        $str = mb_convert_encoding($str, 'UTF-8', $encoding ?: 'ISO-8859-1');
                    }

                    return htmlspecialchars_decode(mb_convert_encoding($str, 'UTF-8', 'UTF-8'), ENT_QUOTES);
                }, $row);

                Provider::updateOrCreate(['tax_id' => $cleanRow['tax_id']], $cleanRow);
            }
        });
        fclose($file);
    }
}