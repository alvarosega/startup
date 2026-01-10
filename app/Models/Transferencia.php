<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transferencia extends Model
{
    protected $guarded = [];
    
    public function detalles() { return $this->hasMany(DetalleTransferencia::class); }
    public function origen() { return $this->belongsTo(Branch::class, 'origen_branch_id'); }
    public function destino() { return $this->belongsTo(Branch::class, 'destino_branch_id'); }
}