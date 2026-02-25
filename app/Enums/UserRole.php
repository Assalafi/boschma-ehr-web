<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'Super Admin';
    case ADMIN = 'Admin';
    case DOCTOR = 'Doctor';
    case SURGEON = 'Surgeon';
    case NURSE = 'Nurse';
    case HEAD_NURSE = 'Head Nurse';
    case LAB_TECHNICIAN = 'Lab Technician';
    case LAB_MANAGER = 'Lab Manager';
    case PHARMACIST = 'Pharmacist';
    case PHARM_TECHNICIAN = 'Pharm. Technician';
    case RECEPTIONIST = 'Receptionist';
    case RADIOLOGIST = 'Radiologist';
    case RADIOGRAPHER = 'Radiographer';
    case PATIENT = 'Patient';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    public function description(): string
    {
        return match($this) {
            self::SUPER_ADMIN => 'Full system access and administration',
            self::ADMIN => 'Hospital administration and management',
            self::DOCTOR => 'Medical doctor with patient care privileges',
            self::SURGEON => 'Surgical specialist with operating room privileges',
            self::NURSE => 'Nursing staff with patient care duties',
            self::HEAD_NURSE => 'Senior nursing staff with supervisory duties',
            self::LAB_TECHNICIAN => 'Laboratory testing and analysis',
            self::LAB_MANAGER => 'Laboratory management and supervision',
            self::PHARMACIST => 'Pharmacy and medication management',
            self::PHARM_TECHNICIAN => 'Pharmacy technician support',
            self::RECEPTIONIST => 'Front desk and patient registration',
            self::RADIOLOGIST => 'Radiology specialist interpreting imaging studies',
            self::RADIOGRAPHER => 'Radiographer operating imaging equipment',
            self::PATIENT => 'Patient access to personal records',
        };
    }

    public function isMedicalStaff(): bool
    {
        return in_array($this, [
            self::DOCTOR,
            self::SURGEON,
            self::NURSE,
            self::HEAD_NURSE,
            self::LAB_TECHNICIAN,
            self::LAB_MANAGER,
            self::PHARMACIST,
            self::PHARM_TECHNICIAN,
            self::RADIOLOGIST,
            self::RADIOGRAPHER,
        ]);
    }

    public function isAdministrative(): bool
    {
        return in_array($this, [
            self::SUPER_ADMIN,
            self::ADMIN,
            self::RECEPTIONIST,
        ]);
    }

    public function permissionLevel(): int
    {
        return match($this) {
            self::SUPER_ADMIN => 100,
            self::ADMIN => 90,
            self::SURGEON => 85,
            self::DOCTOR => 80,
            self::RADIOLOGIST => 78,
            self::HEAD_NURSE => 72,
            self::NURSE => 70,
            self::PHARMACIST => 65,
            self::RADIOGRAPHER => 62,
            self::LAB_MANAGER => 62,
            self::LAB_TECHNICIAN => 60,
            self::PHARM_TECHNICIAN => 58,
            self::RECEPTIONIST => 50,
            self::PATIENT => 10,
        };
    }

    public function canAccessSensitiveData(): bool
    {
        return match($this) {
            self::SUPER_ADMIN, self::ADMIN, self::DOCTOR, self::SURGEON, self::NURSE, self::HEAD_NURSE => true,
            self::RADIOLOGIST, self::RADIOGRAPHER => true,
            default => false,
        };
    }

    public function canModifyMedicalRecords(): bool
    {
        return match($this) {
            self::SUPER_ADMIN, self::ADMIN, self::DOCTOR, self::SURGEON => true,
            self::RADIOLOGIST => true,
            default => false,
        };
    }

    public function canPrescribeMedication(): bool
    {
        return match($this) {
            self::DOCTOR, self::SURGEON => true,
            default => false,
        };
    }

    public function canManageAppointments(): bool
    {
        return in_array($this, [
            self::SUPER_ADMIN,
            self::ADMIN,
            self::DOCTOR,
            self::SURGEON,
            self::NURSE,
            self::HEAD_NURSE,
            self::RECEPTIONIST,
        ]);
    }
}
