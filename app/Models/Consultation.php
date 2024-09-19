<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'receptionist_id',
        'doctor_id',
        'doctor_name',
        'type_of_consultations_id',
        'insurance_id',
        'dates',
        'RDVsphere',
        'RDVcylinder',
        'RDVaxis',
        'LDVsphere',
        'LDVcylinder',
        'LDVaxis',
        'RNV',
        'LNV',
        'distant_comment',
        'near_comment',
        'prescription_status',
        'status',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
        
    }
    public function consultation_type()
    {
        return $this->belongsTo(TypeOfConsultation::class, 'type_of_consultations_id');
        
    }

    public function prescription()
    {
        return $this->hasOne(Prescription::class, 'consultation_id');        
    }
}
