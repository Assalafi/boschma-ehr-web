<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Child extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'beneficiary_id',
        'facility_id',
        'boschma_no',
        'nin',
        'name',
        'gender',
        'photo',
        'created_at',
        'updated_at',
        'dob',
        'remarks',
        'created_by',
        'updated_by',
        'submitted_by',
        'birth_certificate_file',
        'birth_certificate_no',
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
