<?php

namespace App\Enums;

enum EncounterStatus: string
{
    case PENDING = 'pending';
    case TRIAGE = 'triage';
    case WAITING = 'waiting';
    case IN_CONSULTATION = 'in_consultation';
    case AWAITING_LAB = 'awaiting_lab';
    case AWAITING_PHARMACY = 'awaiting_pharmacy';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::TRIAGE => 'In Triage',
            self::WAITING => 'Waiting for Doctor',
            self::IN_CONSULTATION => 'In Consultation',
            self::AWAITING_LAB => 'Awaiting Lab Results',
            self::AWAITING_PHARMACY => 'Awaiting Pharmacy',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'secondary',
            self::TRIAGE => 'info',
            self::WAITING => 'warning',
            self::IN_CONSULTATION => 'primary',
            self::AWAITING_LAB => 'purple',
            self::AWAITING_PHARMACY => 'orange',
            self::COMPLETED => 'success',
            self::CANCELLED => 'danger',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
