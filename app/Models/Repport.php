<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repport extends Model
{
    use HasFactory;
    protected $fillable = [
        'type',
        'total_sales',
        'cash_at_hand',
        'cash_at_partners',
        'total_expence',
        'total_tax',
        'from_date',
        'to_date',
        'date',
        'sms_sent_status'

    ];
}
