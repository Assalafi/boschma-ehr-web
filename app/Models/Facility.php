<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'code',
        'type',
        'address',
        'city',
        'state',
        'country',
        'phone',
        'email',
        'status',
    ];

    public function beneficiaries()
    {
        return $this->hasMany(Beneficiary::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function encounters()
    {
        return $this->hasMany(Encounter::class);
    }

    public function drugs()
    {
        return $this->hasMany(Drug::class);
    }
}
