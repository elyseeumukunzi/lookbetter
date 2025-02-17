<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cell extends Model
{
    use HasFactory;

    public function sector()
    {
        return $this->belongsTo(Sector::class , 'SectorCode');
    }
    public function village()
    {
        return $this->hasMany(Village::class, 'cellcode');
    }
}
