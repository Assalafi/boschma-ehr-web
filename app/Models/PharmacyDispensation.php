<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PharmacyDispensation extends Model
{
    use HasUuids;

    protected $fillable = [
        'prescription_item_id',
        'quantity_dispensed',
        'cost_of_medication',
        'payment_method',
        'copayment_amount',
        'dispensing_date_time',
        'dispensing_officer_id',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'dispensing_date_time' => 'datetime',
        'cost_of_medication' => 'decimal:2',
        'copayment_amount' => 'decimal:2',
    ];

    public function prescriptionItem()
    {
        return $this->belongsTo(PrescriptionItem::class, 'prescription_item_id');
    }

    public function dispensingOfficer()
    {
        return $this->belongsTo(User::class, 'dispensing_officer_id');
    }
}
