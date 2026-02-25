<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_order_id',
        'service_item_id',
        'authorization_code',
        'authorization_expires_at',
        'status',
    ];

    protected $casts = [
        'authorization_expires_at' => 'datetime',
    ];

    public function serviceOrder()
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function serviceItem()
    {
        return $this->belongsTo(ServiceItem::class);
    }

    public function serviceResults()
    {
        return $this->hasMany(ServiceResult::class);
    }

    public function latestResult()
    {
        return $this->hasOne(ServiceResult::class)->latest();
    }
}
