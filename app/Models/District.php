<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;

    protected function province()
    {
        return $this->belongsTo(Province::class , 'ProvinceCode');
    }
    protected function sector()
    {
        return $this->hasMany(Sector::class , 'DistrictId');
    }
}
