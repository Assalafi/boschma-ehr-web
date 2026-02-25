<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsultationProcedure extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'clinical_consultation_id',
        'procedure_name',
        'procedure_date',
        'performed_by',
        'notes',
    ];

    protected $casts = [
        'procedure_date' => 'datetime',
    ];

    public function consultation()
    {
        return $this->belongsTo(ClinicalConsultation::class, 'clinical_consultation_id');
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
