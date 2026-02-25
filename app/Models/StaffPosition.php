<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffPosition extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'department',
        'description',
        'status',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'staff_position_id');
    }
}
