<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;


class Transaction extends Model
{
    //
    protected $fillable = [
        'name',
        'phone',
        'sub_total',
        'tax_total',
        'grand_total',
        'merchant_id',
    ];
    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }
    public function transactionProducts()
    {
        return $this->hasMany(TransactionProduct::class);
    }

    public function products()
{
    return $this->belongsToMany(Product::class, 'transaction_products')
                ->withPivot('quantity', 'price') // sesuaikan dengan kolom pivot
                ->withTimestamps();
}
}
