<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Beneficiary extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'facility_id',
        'alt_facility_id',
        'program_id',
        'boschma_no',
        'sequence_number',
        'fullname',
        'date_of_birth',
        'gender',
        'phone_no',
        'email',
        'contact_address',
        'city',
        'state',
        'country',
        'id_type',
        'id_no',
        'nin',
        'photo',
        'signature',
        'status',
        'has_spouse',
        'number_of_children',
        'remarks',
        'place_of_birth',
        'lga',
        'nationality',
        'marital_status',
        'ethnicity',
        'religion',
        'occupation',
        'dp_no',
        'place_of_work',
        'date_of_employment',
        'date_of_retirement',
        'category',
        'signature_date',
        'created_by',
        'submitted_by',
        'updated_by',
        'created_at'
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function altFacility()
    {
        return $this->belongsTo(Facility::class, 'alt_facility_id');
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function spouse()
    {
        return $this->hasOne(Spouse::class);
    }

    public function children()
    {
        return $this->hasMany(Child::class);
    }

    public function patient()
    {
        return $this->hasOne(Patient::class, 'enrollee_number', 'boschma_no');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
