<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacilityClaim extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'encounter_id',
        'claim_amount',
        'status',
        'submitted_date',
        'processed_date',
    ];

    protected $casts = [
        'claim_amount' => 'decimal:2',
        'submitted_date' => 'datetime',
        'processed_date' => 'datetime',
    ];

    public function encounter()
    {
        return $this->belongsTo(Encounter::class);
    }
}
