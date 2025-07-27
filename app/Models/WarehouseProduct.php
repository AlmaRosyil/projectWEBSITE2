<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseProduct extends Model
{
    //
    protected $fillable = [
        'warehouse_id',
        'product_id',
        'stock',
    ];
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
