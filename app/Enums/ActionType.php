<?php

namespace App\Enums;

enum ActionType: string
{
    case REGISTRATION  = 'Registration';
    case TRIAGE        = 'Triage';
    case CONSULTATION  = 'Consultation';
    case INVESTIGATION = 'Investigation';
    case LAB_TEST      = 'Lab Test';
    case LAB_RESULT_ADDED = 'Lab Result Added';
    case LAB_STATUS_UPDATE = 'Lab Status Update';
    case IMAGING       = 'Imaging';
    case IMAGING_SUBMITTED = 'Imaging Submitted';
    case RADIOLOGY_REPORT = 'Radiology Report';
    case PRESCRIPTION  = 'Prescription';
    case PHARMACY      = 'Pharmacy';
    case REFERRAL      = 'Referral';
    case ADMISSION     = 'Admission';
    case DISCHARGE     = 'Discharge';
    case NOTES         = 'Notes';
    case FOLLOW_UP     = 'Follow-up';

    public function label(): string
    {
        return match($this) {
            self::REGISTRATION  => 'Patient Registration',
            self::TRIAGE        => 'Triage Assessment',
            self::CONSULTATION  => 'Doctor Consultation',
            self::INVESTIGATION => 'Investigation / Order',
            self::LAB_TEST      => 'Laboratory Test',
            self::LAB_RESULT_ADDED => 'Laboratory Result Added',
            self::LAB_STATUS_UPDATE => 'Laboratory Status Updated',
            self::IMAGING       => 'Imaging Study',
            self::IMAGING_SUBMITTED => 'Imaging Submitted',
            self::RADIOLOGY_REPORT => 'Radiology Report Finalized',
            self::PRESCRIPTION  => 'Prescription Written',
            self::PHARMACY      => 'Pharmacy Dispensing',
            self::REFERRAL      => 'Patient Referral',
            self::ADMISSION     => 'Patient Admission',
            self::DISCHARGE     => 'Patient Discharge',
            self::NOTES         => 'Clinical Notes',
            self::FOLLOW_UP     => 'Follow-up Appointment',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
