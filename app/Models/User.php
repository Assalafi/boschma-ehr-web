<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;
    
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'staff_position_id',
        'role_id',
        'facility_id',
        'passport',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'string',
            'staff_position_id' => 'string',
            'role_id' => 'string',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Get the staff position for this user.
     */
    public function staffPosition()
    {
        return $this->belongsTo(StaffPosition::class, 'staff_position_id');
    }
    
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Get the facility for this user.
     */
    public function facility()
    {
        return $this->belongsTo(Facility::class, 'facility_id');
    }

    /**
     * Get the civil servant associated with the user.
     */
    public function civilServant()
    {
        return $this->belongsTo(CivilServant::class);
    }

    /**
     * Get the beneficiaries enrolled by this user (enumerator).
     */
    public function beneficiaries()
    {
        return $this->hasMany(Beneficiary::class, 'created_by');
    }

    /**
     * Get clinical consultations for this user (doctor).
     */
    public function consultations()
    {
        return $this->hasMany(ClinicalConsultation::class, 'doctor_id');
    }

    /**
     * Get vital signs recorded by this user (nurse).
     */
    public function vitalSigns()
    {
        return $this->hasMany(VitalSign::class, 'taken_by');
    }

    /**
     * Get pharmacy dispensations by this user (pharmacist).
     */
    public function dispensations()
    {
        return $this->hasMany(PharmacyDispensation::class, 'dispensing_officer_id');
    }

    /**
     * Get encounters where this user is the officer in charge.
     */
    public function encounters()
    {
        return $this->hasMany(Encounter::class, 'officer_in_charge_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            // Generate UUID if not already set
            if (empty($user->id) || $user->id === '0') {
                $user->id = (string) Str::uuid();
                \Log::info('Generated UUID for user: ' . $user->id);
            }

            // Set email_verified_at to current time if not provided
            if (empty($user->email_verified_at)) {
                $user->email_verified_at = now();
            }
        });
    }
    
    /**
     * Get the route key for the model.
     * Using id for routing
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * Get the user's primary role name.
     */
    public function getPrimaryRoleAttribute()
    {
        return $this->roles->first()?->name ?? 'User';
    }

    /**
     * Check if user has specific role.
     */
    public function hasSpecificRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }

    /**
     * Check if user is a doctor.
     */
    public function isDoctor(): bool
    {
        return $this->hasSpecificRole('Doctor') || $this->hasSpecificRole('Surgeon');
    }

    /**
     * Check if user is a nurse.
     */
    public function isNurse(): bool
    {
        return $this->hasSpecificRole('Nurse') || $this->hasSpecificRole('Head Nurse');
    }

    /**
     * Check if user is a pharmacist.
     */
    public function isPharmacist(): bool
    {
        return $this->hasSpecificRole('Pharmacist') || $this->hasSpecificRole('Pharm. Technician');
    }

    /**
     * Check if user is lab technician.
     */
    public function isLabTechnician(): bool
    {
        return $this->hasSpecificRole('Lab Technician') || $this->hasSpecificRole('Lab Manager');
    }

    /**
     * Check if user is receptionist.
     */
    public function isReceptionist(): bool
    {
        return $this->hasSpecificRole('Receptionist');
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasSpecificRole('Admin') || $this->hasSpecificRole('Super Admin') || $this->hasSpecificRole('Administrator');
    }
}
