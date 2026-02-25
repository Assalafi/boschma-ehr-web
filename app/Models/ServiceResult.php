<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_order_item_id',
        'result_value',
        'result_data',
        'result_note',
        'reference_range',
        'remark',
        'result_document_url',
        'reported_by',
        'reported_at',
        'status',
    ];

    protected $casts = [
        'result_data' => 'array',
        'reported_at' => 'datetime',
    ];

    public function serviceOrderItem()
    {
        return $this->belongsTo(ServiceOrderItem::class);
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
