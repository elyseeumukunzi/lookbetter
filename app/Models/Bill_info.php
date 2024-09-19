<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill_info extends Model
{
    use HasFactory;
    protected $fillable = [
        'bill_id',
        'product_id',
        'quantity',
        'subtotal'
    ];
    public function product()
    {
        return $this->belongsTo(Stock::class, 'product_id');
    }

}
