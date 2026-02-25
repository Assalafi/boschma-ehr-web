<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DrugAdministration extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'prescription_item_id',
        'administering_officer_id',
        'dose_given',
        'administration_date_time',
        'notes',
    ];

    protected $casts = [
        'administration_date_time' => 'datetime',
    ];

    public function prescriptionItem()
    {
        return $this->belongsTo(PrescriptionItem::class);
    }

    public function administeringOfficer()
    {
        return $this->belongsTo(User::class, 'administering_officer_id');
    }
}
