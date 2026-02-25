<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Spouse extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'beneficiary_id',
        'facility_id',
        'boschma_no',
        'nin',
        'name',
        'gender',
        'phone',
        'email',
        'photo',
        'created_at',
        'updated_at',
        'dob',
        'remarks',
        'created_by',
        'updated_by',
        'submitted_by',
    ];

    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class);
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function patient()
    {
        return $this->hasOne(Patient::class, 'enrollee_number', 'boschma_no');
    }
}
