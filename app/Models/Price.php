<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    protected $fillable = [
        'sku_id', 'branch_id', 'list_price', 
        'final_price', 'min_quantity', 'valid_from', 'valid_to'
    ];
    protected $guarded = [];
}