<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DrugStock extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'drug_id',
        'facility_id',
        'batch_number',
        'expiry_date',
        'quantity',
        'quantity_remaining',
        'cost_price',
        'selling_price',
        'status',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'quantity' => 'integer',
        'quantity_remaining' => 'integer',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
    ];

    public function drug()
    {
        return $this->belongsTo(Drug::class);
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
}
