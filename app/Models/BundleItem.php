<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class BundleItem extends Pivot
{
    
    protected $table = 'bundle_items';
    
    public $incrementing = false;
    
    protected $primaryKey = null; 
}