<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Prescription extends Model
{
    use HasUuids;

    protected $fillable = [
        'clinical_consultation_id',
        'prescription_number',
        'prescribed_by',
        'status',
        'prescription_date',
    ];

    public function prescribedBy()
    {
        return $this->belongsTo(User::class, 'prescribed_by');
    }

    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'prescription_date' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'Pending';
    const STATUS_PARTIAL = 'Partially Dispensed';
    const STATUS_DISPENSED = 'Dispensed';
    const STATUS_CANCELLED = 'Cancelled';

    public function consultation()
    {
        return $this->belongsTo(ClinicalConsultation::class, 'clinical_consultation_id');
    }

    public function items()
    {
        return $this->hasMany(PrescriptionItem::class, 'prescription_id');
    }

    public function dispensations()
    {
        return $this->hasManyThrough(PharmacyDispensation::class, PrescriptionItem::class, 'prescription_id', 'prescription_item_id');
    }

    /**
     * Get total number of items
     */
    public function getTotalItemsAttribute()
    {
        return $this->items()->count();
    }

    /**
     * Get number of dispensed items
     */
    public function getDispensedItemsAttribute()
    {
        return $this->items()->where('dispensing_status', 'Dispensed')->count();
    }

    /**
     * Get number of pending items
     */
    public function getPendingItemsAttribute()
    {
        return $this->items()->where('dispensing_status', 'Pending')->count();
    }

    /**
     * Check if prescription is fully dispensed
     */
    public function isFullyDispensed(): bool
    {
        return $this->getTotalItemsAttribute() > 0 && $this->getDispensedItemsAttribute() === $this->getTotalItemsAttribute();
    }

    /**
     * Check if prescription is partially dispensed
     */
    public function isPartiallyDispensed(): bool
    {
        return $this->getDispensedItemsAttribute() > 0 && $this->getDispensedItemsAttribute() < $this->getTotalItemsAttribute();
    }

    /**
     * Update status based on items
     */
    public function updateStatus()
    {
        if ($this->isFullyDispensed()) {
            $this->status = self::STATUS_DISPENSED;
        } elseif ($this->isPartiallyDispensed()) {
            $this->status = self::STATUS_PARTIAL;
        } else {
            $this->status = self::STATUS_PENDING;
        }
        $this->save();
    }
}
