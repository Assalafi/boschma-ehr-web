<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IcdCode extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'code',
        'description',
        'chapter',
        'block',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get clinical diagnoses using this ICD code
     */
    public function diagnoses()
    {
        return $this->hasMany(ClinicalDiagnosis::class, 'icd_code_id');
    }

    /**
     * Scope to search ICD codes
     */
    public function scopeSearch($query, $term)
    {
        return $query->where('code', 'LIKE', "%{$term}%")
                    ->orWhere('description', 'LIKE', "%{$term}%");
    }

    /**
     * Scope to active codes only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
