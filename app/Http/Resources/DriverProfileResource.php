<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DriverProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            
            // Datos operativos
            'license_number' => $this->license_number,
            'license_plate' => $this->license_plate,
            'vehicle_type' => $this->vehicle_type,
            'status' => $this->status, // 'pending', 'verified', 'rejected'
            'rejection_reason' => $this->rejection_reason,
            
            // Lógica de Documentos: Convertir PATH (BD) a URL (Frontend)
            // Usamos 'Storage::url' si el archivo existe, si no, enviamos null.
            
            'ci_front_url' => $this->ci_front_path 
                ? Storage::url($this->ci_front_path) 
                : null,
                
            'license_photo_url' => $this->license_photo_path 
                ? Storage::url($this->license_photo_path) 
                : null,
                
            'vehicle_photo_url' => $this->vehicle_photo_path 
                ? Storage::url($this->vehicle_photo_path) 
                : null,

            // Flag útil para el Dashboard (saber si ya subió docs o no)
            'has_documents' => !empty($this->ci_front_path) 
                            && !empty($this->license_photo_path),
        ];
    }
}