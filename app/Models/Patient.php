<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Patient extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'file_number',
        'enrollee_number',
        'enrollee_type',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Get the enrollee details based on type
     */
    public function getEnrolleeDetailsAttribute()
    {
        switch ($this->enrollee_type) {
            case 'beneficiary':
                return Beneficiary::where('boschma_no', $this->enrollee_number)->first();
            case 'spouse':
                return Spouse::where('boschma_no', $this->enrollee_number)->first();
            case 'child':
                return Child::where('boschma_no', $this->enrollee_number)->first();
            default:
                return null;
        }
    }

    /**
     * Get full enrollee information with all details
     */
    public function getFullInfoAttribute()
    {
        $details = $this->enrolleeDetails;
        
        if (!$details) {
            return null;
        }

        return [
            'id' => $this->id,
            'file_number' => $this->file_number,
            'enrollee_number' => $this->enrollee_number,
            'enrollee_type' => $this->enrollee_type,
            'fullname' => $details->fullname ?? '',
            'boschma_no' => $this->enrollee_number,
            'nin' => $details->nin ?? '',
            'gender' => $details->gender ?? '',
            'date_of_birth' => $details->date_of_birth ?? '',
            'phone_no' => $details->phone_no ?? $details->phone ?? '',
            'email' => $details->email ?? '',
        ];
    }

    /**
     * Get the beneficiary relationship
     */
    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class, 'enrollee_number', 'boschma_no');
    }

    /**
     * Get encounters for this patient
     */
    public function encounters()
    {
        return $this->hasMany(Encounter::class);
    }

    /**
     * Get programs patient is enrolled in through encounters
     */
    public function programs()
    {
        return $this->belongsToMany(Program::class, 'encounters', 'patient_id', 'program_id')
            ->distinct();
    }

    /**
     * Scope to search patients
     */
    public function scopeSearch($query, $term)
    {
        return $query->where('enrollee_number', 'LIKE', "%{$term}%")
                    ->orWhere('file_number', 'LIKE', "%{$term}%");
    }
}
