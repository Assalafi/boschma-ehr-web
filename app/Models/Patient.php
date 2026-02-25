<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Patient extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['file_number', 'enrollee_number', 'enrollee_type'];
    protected $keyType = 'string';
    public $incrementing = false;

    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class, 'enrollee_number', 'boschma_no');
    }

    public function spouse()
    {
        return $this->belongsTo(Spouse::class, 'enrollee_number', 'boschma_no');
    }

    public function child()
    {
        return $this->belongsTo(Child::class, 'enrollee_number', 'boschma_no');
    }

    public function encounters()
    {
        return $this->hasMany(Encounter::class);
    }

    public function programs()
    {
        return $this->belongsToMany(Program::class, 'encounters', 'patient_id', 'program_id')->distinct();
    }

    public function getEnrolleeAttribute()
    {
        if ($this->enrollee_type === 'spouse') {
            return $this->relationLoaded('spouse') ? $this->spouse : Spouse::where('boschma_no', $this->enrollee_number)->first();
        }
        if ($this->enrollee_type === 'child') {
            return $this->relationLoaded('child') ? $this->child : Child::where('boschma_no', $this->enrollee_number)->first();
        }
        return $this->relationLoaded('beneficiary') ? $this->beneficiary : Beneficiary::where('boschma_no', $this->enrollee_number)->first();
    }

    public function getUltimateBeneficiaryAttribute()
    {
        $e = $this->enrollee;
        if ($e instanceof Spouse || $e instanceof Child) {
            return $e->beneficiary;
        }
        return $e;
    }

    public function getEnrolleeNameAttribute()
    {
        $e = $this->enrollee;
        return $e->fullname ?? $e->name ?? 'Unknown';
    }

    public function getEnrolleeGenderAttribute()
    {
        return $this->enrollee->gender ?? '';
    }

    public function getEnrolleeDobAttribute()
    {
        $e = $this->enrollee;
        return $e->date_of_birth ?? $e->dob ?? null;
    }

    public function getEnrolleePhoneAttribute()
    {
        $e = $this->enrollee;
        return $e->phone_no ?? $e->phone ?? '';
    }

    public function getEnrolleeEmailAttribute()
    {
        return $this->enrollee->email ?? '';
    }

    public function getEnrolleeNinAttribute()
    {
        return $this->enrollee->nin ?? '';
    }

    public function getEnrolleePhotoAttribute()
    {
        return $this->enrollee->photo ?? null;
    }

    public function getEnrolleeDetailsAttribute()
    {
        return $this->enrollee;
    }

    public function getFullInfoAttribute()
    {
        $e = $this->enrollee;
        if (!$e) return null;
        return [
            'id' => $this->id, 'file_number' => $this->file_number,
            'enrollee_number' => $this->enrollee_number, 'enrollee_type' => $this->enrollee_type,
            'fullname' => $e->fullname ?? $e->name ?? '', 'boschma_no' => $this->enrollee_number,
            'nin' => $e->nin ?? '', 'gender' => $e->gender ?? '',
            'date_of_birth' => $e->date_of_birth ?? $e->dob ?? '',
            'phone_no' => $e->phone_no ?? $e->phone ?? '', 'email' => $e->email ?? '',
        ];
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('file_number', 'LIKE', "%{$term}%")
              ->orWhere('enrollee_number', 'LIKE', "%{$term}%")
              ->orWhereHas('beneficiary', fn($b) =>
                  $b->where('fullname', 'LIKE', "%{$term}%")
                    ->orWhere('phone_no', 'LIKE', "%{$term}%")
                    ->orWhere('nin', 'LIKE', "%{$term}%")
              )
              ->orWhereHas('spouse', fn($s) =>
                  $s->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('phone', 'LIKE', "%{$term}%")
                    ->orWhere('nin', 'LIKE', "%{$term}%")
              )
              ->orWhereHas('child', fn($c) =>
                  $c->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('nin', 'LIKE', "%{$term}%")
              );
        });
    }
}
