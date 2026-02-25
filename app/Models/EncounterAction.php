<?php

namespace App\Models;

use App\Enums\ActionType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EncounterAction extends Model
{
    use HasFactory, HasUuids;
    
    protected $fillable = [
        'encounter_id',
        'user_id',
        'action_type',
        'description',
        'action_time',
    ];

    protected $casts = [
        'action_type' => ActionType::class,
        'action_time' => 'datetime',
    ];

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
