<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class VitalSign extends Model
{
    use HasUuids;

    protected $fillable = [
        'encounter_id',
        'taken_by',
        'temperature',
        'blood_pressure_systolic',
        'blood_pressure_diastolic',
        'pulse_rate',
        'respiration_rate',
        'spo2',
        'weight',
        'height',
        'overall_priority'
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'temperature' => 'decimal:1',
        'weight' => 'decimal:2',
        'height' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function encounter()
    {
        return $this->belongsTo(Encounter::class, 'encounter_id');
    }

    public function takenBy()
    {
        return $this->belongsTo(User::class, 'taken_by');
    }

    public function getBloodPressureAttribute()
    {
        return $this->blood_pressure_systolic . '/' . $this->blood_pressure_diastolic;
    }

    public function getBmiAttribute()
    {
        if ($this->weight && $this->height) {
            $heightInMeters = $this->height / 100;
            return round($this->weight / ($heightInMeters * $heightInMeters), 2);
        }
        return null;
    }

    /**
     * Get triage priority based on vital signs
     */
    public function getTriagePriorityAttribute()
    {
        // Red priority (Emergency)
        if (
            $this->temperature > 38.5 || $this->temperature < 35.0 ||
            $this->blood_pressure_systolic > 180 || $this->blood_pressure_systolic < 90 ||
            $this->blood_pressure_diastolic > 110 || $this->blood_pressure_diastolic < 60 ||
            $this->pulse_rate > 120 || $this->pulse_rate < 50 ||
            $this->respiration_rate > 24 || $this->respiration_rate < 12 ||
            $this->spo2 < 90
        ) {
            return 'Red';
        }
        
        // Yellow priority (Urgent)
        if (
            $this->temperature > 37.5 || $this->temperature < 36.0 ||
            $this->blood_pressure_systolic > 160 || $this->blood_pressure_systolic < 100 ||
            $this->blood_pressure_diastolic > 100 || $this->blood_pressure_diastolic < 65 ||
            $this->pulse_rate > 100 || $this->pulse_rate < 60 ||
            $this->respiration_rate > 20 || $this->respiration_rate < 14 ||
            $this->spo2 < 95
        ) {
            return 'Yellow';
        }
        
        // Green priority (Non-urgent)
        return 'Green';
    }

    /**
     * Get priority color for UI
     */
    public function getPriorityColorAttribute()
    {
        return match($this->triage_priority) {
            'Red' => '#dc3545',
            'Yellow' => '#ffc107',
            'Green' => '#28a745',
            default => '#6c757d'
        };
    }

    /**
     * Check if any vital signs are abnormal
     */
    public function hasAbnormalVitals(): bool
    {
        return $this->triage_priority !== 'Green';
    }

    /**
     * Get abnormal vital signs list
     */
    public function getAbnormalVitalsListAttribute()
    {
        $abnormal = [];
        
        if ($this->temperature > 37.5 || $this->temperature < 36.0) {
            $abnormal[] = 'Temperature: ' . $this->temperature . '°C';
        }
        
        if ($this->blood_pressure_systolic > 140 || $this->blood_pressure_systolic < 100) {
            $abnormal[] = 'BP: ' . $this->blood_pressure;
        }
        
        if ($this->pulse_rate > 100 || $this->pulse_rate < 60) {
            $abnormal[] = 'Pulse: ' . $this->pulse_rate . ' bpm';
        }
        
        if ($this->respiration_rate > 20 || $this->respiration_rate < 14) {
            $abnormal[] = 'RR: ' . $this->respiration_rate . '/min';
        }
        
        if ($this->spo2 < 95) {
            $abnormal[] = 'SpO2: ' . $this->spo2 . '%';
        }
        
        return $abnormal;
    }
}
