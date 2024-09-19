<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'brand_id',
        'productname',
        'model',
        'price',
        'selling_unit',
        'status'
    ];

    public function products()
    {
        return $this->hasMany(Bill_info::class );
    }
}
