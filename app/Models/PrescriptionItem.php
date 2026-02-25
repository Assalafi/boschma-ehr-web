<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PrescriptionItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'prescription_id',
        'drug_id',
        'dosage',
        'frequency',
        'duration',
        'quantity',
        'instructions',
        'dispensing_status',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    // Status constants
    const STATUS_PENDING = 'Pending';
    const STATUS_DISPENSED = 'Dispensed';
    const STATUS_ADMINISTERED = 'Administered';

    public function prescription()
    {
        return $this->belongsTo(Prescription::class, 'prescription_id');
    }

    public function drug()
    {
        return $this->belongsTo(Drug::class, 'drug_id');
    }

    public function dispensations()
    {
        return $this->hasMany(PharmacyDispensation::class, 'prescription_item_id');
    }

    public function administrations()
    {
        return $this->hasMany(DrugAdministration::class, 'prescription_item_id');
    }

    /**
     * Get total dispensed quantity
     */
    public function getTotalDispensedAttribute()
    {
        return $this->dispensations()->sum('quantity_dispensed');
    }

    /**
     * Get total administered quantity
     */
    public function getTotalAdministeredAttribute()
    {
        return $this->administrations()->sum('dose_given');
    }

    /**
     * Check if fully dispensed
     */
    public function isFullyDispensed(): bool
    {
        return $this->getTotalDispensedAttribute() >= $this->quantity;
    }

    /**
     * Check if fully administered
     */
    public function isFullyAdministered(): bool
    {
        return $this->getTotalAdministeredAttribute() >= $this->quantity;
    }

    /**
     * Get remaining quantity to dispense
     */
    public function getRemainingToDispenseAttribute()
    {
        return max(0, $this->quantity - $this->getTotalDispensedAttribute());
    }

    /**
     * Get formatted dosage instruction
     */
    public function getFormattedInstructionAttribute()
    {
        return "{$this->dosage} - {$this->frequency} for {$this->duration}";
    }

    /**
     * Update dispensing status
     */
    public function updateDispensingStatus()
    {
        if ($this->isFullyDispensed()) {
            $this->dispensing_status = self::STATUS_DISPENSED;
        } else {
            $this->dispensing_status = self::STATUS_PENDING;
        }
        $this->save();
    }
}
