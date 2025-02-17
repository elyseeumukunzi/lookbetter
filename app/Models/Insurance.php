<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insurance extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contacts',
        'tin',
        'tm',
        'status',
    ];

    public function clients()
    {
        return $this->hasMany(Client::class , 'insurance_id');
    }
}
