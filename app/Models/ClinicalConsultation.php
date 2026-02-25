<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ClinicalConsultation extends Model
{
    use HasUuids;

    protected $fillable = [
        'encounter_id',
        'doctor_id',
        'presenting_complaints',
        'history_of_present_illness',
        'physical_examination',
        'investigation_required',
        'investigation_note',
        'clinical_note',
        'status'
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'investigation_required' => 'boolean',
    ];

    // Status constants
    const STATUS_IN_PROGRESS = 'In Progress';
    const STATUS_COMPLETED = 'Completed';
    const STATUS_PENDING = 'Pending';

    public function encounter()
    {
        return $this->belongsTo(Encounter::class, 'encounter_id');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function diagnoses()
    {
        return $this->hasMany(ClinicalDiagnosis::class, 'clinical_consultation_id');
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'clinical_consultation_id');
    }

    public function procedures()
    {
        return $this->hasMany(ConsultationProcedure::class, 'clinical_consultation_id');
    }

    public function investigations()
    {
        return $this->hasMany(Investigation::class, 'clinical_consultation_id');
    }

    /**
     * Get provisional diagnoses
     */
    public function getProvisionalDiagnosesAttribute()
    {
        return $this->diagnoses()->where('diagnosis_type', 'Provisional')->get();
    }

    /**
     * Get confirmed diagnoses
     */
    public function getConfirmedDiagnosesAttribute()
    {
        return $this->diagnoses()->where('diagnosis_type', 'Confirmed')->get();
    }

    /**
     * Check if consultation has diagnoses
     */
    public function hasDiagnoses(): bool
    {
        return $this->diagnoses()->exists();
    }

    /**
     * Check if consultation has prescriptions
     */
    public function hasPrescriptions(): bool
    {
        return $this->prescriptions()->exists();
    }

    /**
     * Check if consultation requires investigations
     */
    public function requiresInvestigations(): bool
    {
        return $this->investigation_required || $this->investigations()->exists();
    }

    /**
     * Get consultation summary
     */
    public function getSummaryAttribute()
    {
        return [
            'patient_name' => $this->encounter?->patient_name,
            'complaints' => $this->presenting_complaints,
            'diagnoses_count' => $this->diagnoses()->count(),
            'prescriptions_count' => $this->prescriptions()->count(),
            'investigations_count' => $this->investigations()->count(),
            'status' => $this->status,
            'doctor_name' => $this->doctor?->name,
            'consultation_date' => $this->encounter?->visit_date,
        ];
    }
}
