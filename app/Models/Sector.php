<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sector extends Model
{
    use HasFactory;

    public function district()
    {
        return $this->belongsTo(District::class , 'DistrictCode');
    }
    public function cell()
    {
        return $this->hasMany(Cell::class , 'SectorId');
    }
}
