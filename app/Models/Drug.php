<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Auth;

class Drug extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'facility_id',
        'name',
        'description',
        'dosage_form',
        'strength',
        'unit',
        'quantity',
        'unit_price',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class, 'facility_id');
    }

    public function stocks()
    {
        return $this->hasMany(DrugStock::class, 'drug_id');
    }

    public function prescriptionItems()
    {
        return $this->hasMany(PrescriptionItem::class, 'drug_id');
    }

    public function dispensations()
    {
        return $this->hasManyThrough(PharmacyDispensation::class, PrescriptionItem::class, 'drug_id', 'prescription_item_id');
    }

    /**
     * Get the selling price from the drug's unit_price
     */
    public function getSellingPrice($facilityId = null): float
    {
        return (float) ($this->unit_price ?? 0);
    }

    /**
     * Get the current stock for this drug
     */
    public function getCurrentStockAttribute($facilityId = null)
    {
        return $this->totalStockInFacility($facilityId);
    }

    /**
     * Get total available quantity from all stock entries in a specific facility
     */
    public function totalStockInFacility($facilityId = null): int
    {
        if (!$facilityId) {
            $facilityId = \Illuminate\Support\Facades\Auth::user()->facility_id ?? null;
        }
        
        return $this->stocks()
            ->when($facilityId, fn($q) => $q->where('facility_id', $facilityId))
            ->where('status', 'approved')
            ->where('quantity_remaining', '>', 0)
            ->sum('quantity_remaining');
    }

    /**
     * Check if sufficient stock is available in a specific facility
     */
    public function hasStock(int $quantity, $facilityId = null): bool
    {
        return $this->totalStockInFacility($facilityId) >= $quantity;
    }

    /**
     * Deduct quantity from stock using FEFO (First Expiry First Out)
     */
    public function deductStock(int $quantity, $facilityId = null): int
    {
        if (!$facilityId) {
            $facilityId = \Illuminate\Support\Facades\Auth::user()->facility_id ?? null;
        }
        
        $remainingToDeduct = $quantity;
        $totalDeducted = 0;

        $stocks = $this->stocks()
            ->where('facility_id', $facilityId)
            ->where('status', 'approved')
            ->where('quantity_remaining', '>', 0)
            ->orderBy('expiry_date') // FEFO
            ->get();

        foreach ($stocks as $stock) {
            if ($remainingToDeduct <= 0) {
                break;
            }

            $deductFromThis = min($stock->quantity_remaining, $remainingToDeduct);
            $stock->decrement('quantity_remaining', $deductFromThis);
            
            // Update status if exhausted
            if ($stock->fresh()->quantity_remaining <= 0) {
                $stock->update(['status' => 'exhausted']);
            }
            
            $remainingToDeduct -= $deductFromThis;
            $totalDeducted += $deductFromThis;
        }

        return $totalDeducted;
    }

    /**
     * Check if drug is in stock
     */
    public function isInStock($quantity = 1, $facilityId = null): bool
    {
        return $this->getCurrentStockAttribute($facilityId) >= $quantity;
    }

    /**
     * Get stock status
     */
    public function getStockStatusAttribute($facilityId = null)
    {
        $currentStock = $this->getCurrentStockAttribute($facilityId);
        
        if ($currentStock === 0) {
            return 'Out of Stock';
        } elseif ($currentStock < 10) {
            return 'Low Stock';
        } elseif ($currentStock < 50) {
            return 'Medium Stock';
        } else {
            return 'In Stock';
        }
    }

    /**
     * Get stock status color
     */
    public function getStockStatusColorAttribute($facilityId = null)
    {
        return match($this->getStockStatusAttribute($facilityId)) {
            'Out of Stock' => '#dc3545',
            'Low Stock' => '#ffc107',
            'Medium Stock' => '#fd7e14',
            'In Stock' => '#28a745',
            default => '#6c757d'
        };
    }

    /**
     * Get formatted strength and unit
     */
    public function getFormattedStrengthAttribute()
    {
        return trim("{$this->strength} {$this->unit}");
    }

    /**
     * Scope to search drugs
     */
    public function scopeSearch($query, $term)
    {
        return $query->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('description', 'LIKE', "%{$term}%")
                    ->orWhere('strength', 'LIKE', "%{$term}%");
    }

    /**
     * Scope for facility
     */
    public function scopeForFacility($query, $facilityId)
    {
        return $query->where('facility_id', $facilityId);
    }

    /**
     * Scope in stock
     */
    public function scopeInStock($query, $facilityId = null)
    {
        if (!$facilityId) {
            $facilityId = Auth::user()?->facility_id;
        }
        
        return $query->whereHas('stocks', function ($q) use ($facilityId) {
            $q->where('facility_id', $facilityId)
              ->where('quantity_remaining', '>', 0)
              ->where('status', 'approved');
        });
    }
}
