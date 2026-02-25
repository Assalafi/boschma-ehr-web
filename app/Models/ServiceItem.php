<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceItem extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'service_type_id',
        'name',
        'description',
        'form_definition',
        'type',
        'price',
    ];

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function facilityServices()
    {
        return $this->hasMany(FacilityService::class);
    }
}
