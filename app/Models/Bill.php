<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;
    protected $fillable = [
        'consultation_id',
        'dates',
        'consultation_cost',
        'product_cost',
        'client_bill',
        'insurance_bill',
        'total',
        'payment_method',
        'payment_status'
    ];

    // public static function boot()
    // {
    //     parent::boot();
    //     static::updated(function ($bill) {   
    //        // dd($bill->billinfos);       
    //         foreach ($bxill->billinfos as $billinfo) {
    //             $product = $billinfo->product; // Assuming a product relationship in Bill_info
    //             $product->decrement('quantity', $billinfo->quantity);
    //         }
    //     });
    // }



    public function billinfos()
    {
        return $this->hasMany(Bill_info::class, 'bill_id');

    }
}
