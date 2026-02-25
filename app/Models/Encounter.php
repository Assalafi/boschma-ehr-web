<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Encounter extends Model
{
    use HasUuids;

    protected $fillable = [
        'patient_id',
        'facility_id',
        'program_id',
        'visit_date',
        'nature_of_visit',
        'mode_of_entry',
        'reason_for_visit',
        'officer_in_charge_id',
        'status',
        'outcome',
        'follow_up_date',
    ];

    protected $casts = [
        'visit_date' => 'datetime',
        'follow_up_date' => 'date',
    ];

    // Status constants
    const STATUS_PENDING = 'Pending';
    const STATUS_REGISTERED = 'Registered';
    const STATUS_IN_PROGRESS = 'In Progress';
    const STATUS_TRIAGED = 'Triaged';
    const STATUS_WAITING = 'Waiting';
    const STATUS_IN_CONSULTATION = 'In Consultation';
    const STATUS_AWAITING_LAB = 'Awaiting Lab';
    const STATUS_AWAITING_PHARMACY = 'Awaiting Pharmacy';
    const STATUS_COMPLETED = 'Completed';
    const STATUS_CANCELLED = 'Cancelled';
    const STATUS_ADMITTED = 'Admitted';
    const STATUS_REFERRED = 'Referred';
    const STATUS_FOLLOW_UP = 'Follow-up';

    // Relationships
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function officerInCharge()
    {
        return $this->belongsTo(User::class, 'officer_in_charge_id');
    }

    public function consultations()
    {
        return $this->hasMany(ClinicalConsultation::class);
    }

    public function actions()
    {
        return $this->hasMany(EncounterAction::class);
    }

    public function vitalSigns()
    {
        return $this->hasMany(VitalSign::class);
    }

    public function serviceOrders()
    {
        return $this->hasMany(ServiceOrder::class);
    }

    public function investigations()
    {
        return $this->hasMany(Investigation::class);
    }

    public function admissions()
    {
        return $this->hasMany(Admission::class);
    }

    public function facilityClaim()
    {
        return $this->hasOne(FacilityClaim::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_REGISTERED, self::STATUS_IN_PROGRESS, self::STATUS_TRIAGED]);
    }

    public function scopeWithoutClaim($query)
    {
        return $query->doesntHave('facilityClaim');
    }

    public function scopeReadyForClaim($query)
    {
        return $query->completed()->withoutClaim();
    }

    public function scopeForFacility($query, $facilityId)
    {
        return $query->where('facility_id', $facilityId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('visit_date', $date);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('visit_date', today());
    }

    /**
     * Get the patient's full name through enrollee details
     */
    public function getPatientNameAttribute()
    {
        return $this->patient?->full_info['fullname'] ?? 'Unknown Patient';
    }

    /**
     * Get the patient's BOSCHMA number
     */
    public function getPatientBoschmaNoAttribute()
    {
        return $this->patient?->full_info['boschma_no'] ?? '';
    }

    /**
     * Get the patient's enrollee type
     */
    public function getPatientTypeAttribute()
    {
        return $this->patient?->enrollee_type ?? '';
    }

    /**
     * Check if encounter has vital signs
     */
    public function hasVitalSigns(): bool
    {
        return $this->vitalSigns()->exists();
    }

    /**
     * Check if encounter has consultation
     */
    public function hasConsultation(): bool
    {
        return $this->consultations()->exists();
    }

    /**
     * Check if encounter is admitted
     */
    public function isAdmitted(): bool
    {
        return $this->admissions()->where('is_active', true)->exists();
    }
}
