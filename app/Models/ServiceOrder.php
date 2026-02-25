<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceOrder extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'encounter_id',
        'patient_id',
        'facility_id',
        'ordered_by',
        'order_number',
        'status',
    ];

    public function encounter()
    {
        return $this->belongsTo(Encounter::class);
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function orderedBy()
    {
        return $this->belongsTo(User::class, 'ordered_by');
    }

    public function items()
    {
        return $this->hasMany(ServiceOrderItem::class);
    }

    public function serviceOrderItems()
    {
        return $this->hasMany(ServiceOrderItem::class);
    }
}
