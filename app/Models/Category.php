<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    //
    protected $fillable = [
        'name',
        'photo',
        'tagline',
    ];
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function getPhotoUrlAttribute($value)
    {
        if (!$value) {
            return null;        
        }
    
        return url(Storage::url($value));
    }

}
