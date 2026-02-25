<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceReferral extends Model
{
    use HasFactory;

    protected $fillable = [
        'encounter_id',
        'from_facility_id',
        'to_facility_id',
        'referral_type',
        'service_item_id',
        'reason',
        'status',
    ];

    public function encounter()
    {
        return $this->belongsTo(Encounter::class);
    }

    public function fromFacility()
    {
        return $this->belongsTo(Facility::class, 'from_facility_id');
    }

    public function toFacility()
    {
        return $this->belongsTo(Facility::class, 'to_facility_id');
    }

    public function serviceItem()
    {
        return $this->belongsTo(ServiceItem::class);
    }
}
