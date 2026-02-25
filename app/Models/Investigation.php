<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Investigation extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $fillable = [
        'encounter_id',
        'clinical_consultation_id',
        'patient_id',
        'facility_id',
        'ordered_by',
        'type',
        'category',
        'tests',
        'notes',
        'status',
        'requested_by',
    ];

    protected $casts = [
        'tests' => 'array',
    ];

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function encounter()
    {
        return $this->belongsTo(Encounter::class);
    }

    public function consultation()
    {
        return $this->belongsTo(ClinicalConsultation::class, 'clinical_consultation_id');
    }
}
