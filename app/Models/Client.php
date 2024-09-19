<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;
    protected $fillable = [
        'insurance_id',
        'firstname',
        'lastname',
        'sex',
        'nid',
        'dob',
        'province',
        'district',
        'sector',
        'cell',
        'village',
        'cardnumber',
        'affiliatesociety',
        'relationship',
        'mainmember',
        'status',
    ];

    public function insurance()
    {
        return $this->belongsTo(Insurance::class , 'insurance_id');
    }
}
