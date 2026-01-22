<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RejectionReason;

class ComplianceSeeder extends Seeder
{
    public function run(): void
    {
        $reasons = [
            ['code' => 'IMG_BLURRY', 'reason_text' => 'La fotografía está borrosa o desenfocada.'],
            ['code' => 'DOC_EXPIRED', 'reason_text' => 'El documento de identidad ha caducado.'],
            ['code' => 'DOC_CUTOFF', 'reason_text' => 'Los bordes del documento no son visibles.'],
            ['code' => 'FACE_MISMATCH', 'reason_text' => 'La selfie no coincide con la foto del documento.'],
            ['code' => 'UNDERAGE', 'reason_text' => 'La fecha de nacimiento indica minoría de edad.'],
            ['code' => 'SUSPICIOUS', 'reason_text' => 'Indicios de manipulación digital (Photoshop).'],
        ];

        foreach ($reasons as $reason) {
            RejectionReason::updateOrCreate(['code' => $reason['code']], $reason);
        }
    }
}