<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CivilServant extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'nin',
        'name',
        'department',
        'grade_level',
        'step',
        'status',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
